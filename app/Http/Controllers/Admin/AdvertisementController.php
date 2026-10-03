<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Status;
use App\Models\Advertisement;
use Illuminate\Http\Request;
use App\Http\Requests\AdvertisementRequest;
use App\Http\Controllers\BackendController;

class AdvertisementController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['siteTitle'] = 'Advertisements';

        $this->middleware(['permission:advertisement'])->only('index');
        $this->middleware(['permission:advertisement_create'])->only('create', 'store');
        $this->middleware(['permission:advertisement_edit'])->only('edit', 'update');
        $this->middleware(['permission:advertisement_delete'])->only('destroy');
    }

    /**
     * Display a listing of advertisements.
     */
    public function index()
    {
        $this->data['advertisements'] = Advertisement::orderBy('position')
            ->orderBy('sort', 'asc')
            ->get()
            ->groupBy('position');

        return view('admin.advertisement.index', $this->data);
    }

    /**
     * Show the form for creating a new advertisement.
     */
    public function create()
    {
        $this->data['positions'] = Advertisement::positions();
        return view('admin.advertisement.create', $this->data);
    }

    /**
     * Store a newly created advertisement.
     */
    public function store(AdvertisementRequest $request)
    {
        $advertisement              = new Advertisement();
        $advertisement->title       = $request->title;
        $advertisement->description = $request->description;
        $advertisement->link        = $request->link;
        $advertisement->position    = $request->position;
        $advertisement->status      = $request->status;
        $advertisement->save();

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $advertisement->addMediaFromRequest('image')->toMediaCollection('advertisement');
        }

        $advertisement->sort = $advertisement->id;
        $advertisement->save();

        return redirect(route('admin.advertisement.index'))
            ->withSuccess('Advertisement created successfully.');
    }

    /**
     * Show the form for editing an advertisement.
     */
    public function edit(Advertisement $advertisement)
    {
        $this->data['advertisement'] = $advertisement;
        $this->data['positions']     = Advertisement::positions();
        return view('admin.advertisement.edit', $this->data);
    }

    /**
     * Update an existing advertisement.
     */
    public function update(AdvertisementRequest $request, Advertisement $advertisement)
    {
        $advertisement->title       = $request->title;
        $advertisement->description = $request->description;
        $advertisement->link        = $request->link;
        $advertisement->position    = $request->position;
        $advertisement->status      = $request->status;
        $advertisement->save();

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $advertisement->clearMediaCollection('advertisement');
            $advertisement->addMediaFromRequest('image')->toMediaCollection('advertisement');
        }

        return redirect(route('admin.advertisement.index'))
            ->withSuccess('Advertisement updated successfully.');
    }

    /**
     * Remove an advertisement.
     */
    public function destroy(Advertisement $advertisement)
    {
        $advertisement->clearMediaCollection('advertisement');
        $advertisement->delete();

        return redirect(route('admin.advertisement.index'))
            ->withSuccess('Advertisement deleted successfully.');
    }

    /**
     * Update sort order via drag-and-drop.
     */
    public function sort(Request $request)
    {
        if ($request->has('ids')) {
            $arr = explode(',', $request->input('ids'));
            foreach ($arr as $sortOrder => $id) {
                $ad       = Advertisement::find($id);
                if ($ad) {
                    $ad->sort = ++$sortOrder;
                    $ad->save();
                }
            }
            return response()->json(['success' => true, 'message' => 'Sort order updated.']);
        }
        return response()->json(['success' => false], 400);
    }

    /**
     * Return a live JSON preview of active advertisements for a given position.
     * Used by the admin create/edit forms.
     */
    public function preview(Request $request)
    {
        $position = $request->input('position', 'middle');

        $ads = Advertisement::where('position', $position)
            ->where('status', Status::ACTIVE)
            ->orderBy('sort', 'asc')
            ->get()
            ->map(function ($ad) {
                return [
                    'id'          => $ad->id,
                    'title'       => $ad->title,
                    'description' => $ad->description,
                    'link'        => $ad->link,
                    'image'       => $ad->image,
                    'position'    => $ad->position,
                ];
            });

        return response()->json(['advertisements' => $ads]);
    }
}
