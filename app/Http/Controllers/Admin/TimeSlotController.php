<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Status;
use App\Http\Controllers\BackendController;
use App\Http\Requests\TimeSlotRequest;
use App\Models\Restaurant;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\Datatables\Datatables;

class TimeSlotController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['siteTitle'] = 'Time Slots';

        $this->middleware(['permission:time-slots'])->only('index');
        $this->middleware(['permission:time-slots_create'])->only('create', 'store');
        $this->middleware(['permission:time-slots_edit'])->only('edit', 'update');
        $this->middleware(['permission:time-slots_delete'])->only('destroy');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->getTimeSlot($request);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->data['restaurants'] = Restaurant::where(['status' => Status::ACTIVE])->get();
        return view('admin.time-slot.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TimeSlotRequest $request)
    {
        $startTime = date('H:i:s', strtotime($request->start_time));
        $endTime   = date('H:i:s', strtotime($request->end_time));

        if (strtotime($endTime) <= strtotime($startTime)) {
            return back()->withErrors(['end_time' => 'End Time must be after Start Time.'])->withInput();
        }

        $timeSlot = new TimeSlot;
        $timeSlot->start_time = $startTime;
        $timeSlot->end_time   = $endTime;
        $timeSlot->restaurant_id = $request->restaurant_id;
        $timeSlot->status     = $request->status;
        $timeSlot->save();

        return redirect(route('admin.time-slots.index'))->withSuccess('The Data Inserted Successfully');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->data['timeSlot'] = TimeSlot::findOrFail($id);
        $this->data['restaurants'] = Restaurant::where(['status' => Status::ACTIVE])->get();
        return view('admin.time-slot.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(TimeSlotRequest $request, $id)
    {
        $startTime = date('H:i:s', strtotime($request->start_time));
        $endTime   = date('H:i:s', strtotime($request->end_time));

        if (strtotime($endTime) <= strtotime($startTime)) {
            return back()->withErrors(['end_time' => 'End Time must be after Start Time.'])->withInput();
        }

        $timeSlot = TimeSlot::findOrFail($id);
        $timeSlot->start_time = $startTime;
        $timeSlot->end_time   = $endTime;
        $timeSlot->restaurant_id = $request->restaurant_id;
        $timeSlot->status     = $request->status;
        $timeSlot->save();

        return redirect(route('admin.time-slots.index'))->withSuccess('The Data Updated Successfully');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $timeSlot = TimeSlot::findOrFail($id);

        if ($timeSlot->reservations()->exists()) {
            return redirect(route('admin.time-slots.index'))->withError('Cannot delete. This time slot is already used in reservations.');
        }

        $timeSlot->delete();

        return redirect(route('admin.time-slots.index'))->withSuccess('The Data Deleted Successfully');
    }

    private function getTimeSlot(Request $request)
    {
        if (request()->ajax()) {
            $queryArray = [];
            if (!empty($request->status) && (int) $request->status) {
                $queryArray['status'] = $request->status;
            }
            $timeSlots = [];
            if (auth()->user()->myrole == 3 && auth()->user()->restaurant) {
                $queryArray['restaurant_id'] = auth()->user()->restaurant->id;
                $timeSlots = TimeSlot::where($queryArray)->descending()->select();
            } elseif (auth()->user()->myrole != 3) {
                $timeSlots = TimeSlot::where($queryArray)->descending()->select();
            }

            $i = 0;
            return Datatables::of($timeSlots)
                ->addColumn('action', function ($timeSlot) {
                    $button_array = [];
                    $button_array['edit'] = ['route' => route('admin.time-slots.edit', $timeSlot), 'permission' => 'time-slots_edit'];
                    $button_array['delete'] = ['route' => route('admin.time-slots.destroy', $timeSlot), 'permission' => 'time-slots_delete'];

                    return action_button($button_array);
                })

                ->editColumn('id', function ($timeSlot) use (&$i) {
                    return ++$i;
                })

                ->editColumn('restaurant_id', function ($timeSlot) {
                    return Str::limit($timeSlot->restaurant->name ?? null, 30);
                })
                ->filterColumn('restaurant_id', function ($query, $keyword) {
                    $query->whereHas('restaurant', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })

                ->editColumn('start_time', function ($timeSlot) {
                    return date('h:i A', strtotime($timeSlot->start_time));
                })
                ->filterColumn('start_time', function ($query, $keyword) {
                    $query->where('start_time', 'like', "%{$keyword}%");
                })

                ->editColumn('end_time', function ($timeSlot) {
                    return date('h:i A', strtotime($timeSlot->end_time));
                })
                ->filterColumn('end_time', function ($query, $keyword) {
                    $query->where('end_time', 'like', "%{$keyword}%");
                })

                ->editColumn('status', function ($timeSlot) {
                    return $timeSlot->statusName;
                })
                ->filterColumn('status', function ($query, $keyword) {
                    $query->where('status', 'like', "%{$keyword}%");
                })

                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        return view('admin.time-slot.index', $this->data);
    }
}
