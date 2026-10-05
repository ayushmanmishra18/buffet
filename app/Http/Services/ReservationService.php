<?php

namespace App\Http\Services;

use App\Enums\Status;
use App\Enums\TableStatus;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\TimeSlot;


class ReservationService
{

    public function __construct()
    {
    }


    /**
     * Shared core: free table combinations per date/slot/table.
     *
     * @return array [$timeTableArrays, $timeSlots, $tables]
     */
    private function freeCombos($date, $capacity, $restaurant_id)
    {
        $reservations = Reservation::where(['reservation_date' => $date, 'restaurant_id' => $restaurant_id])->get();

        $reservationArrays = [];
        if(!blank($reservations)) {
            foreach($reservations as $reservation) {
                $reservationArrays[$reservation->reservation_date][$reservation->time_slot_id][$reservation->table_id] = $reservation;
            }
        }

        $timeSlots = TimeSlot::where(['restaurant_id' => $restaurant_id, 'status' => Status::ACTIVE])->get();
        $tables    = Table::where([
            'restaurant_id' => $restaurant_id,
            'status'        => TableStatus::ENABLE
        ])->where('capacity', '>=', $capacity)->get();

        $timeTableArrays = [];
        if(!blank($timeSlots) && !blank($tables)) {
            foreach($timeSlots as $slot) {
                foreach($tables as $table) {
                    if(!isset($reservationArrays[$date][$slot->id][$table->id])) {
                        $timeTableArrays[$date][$slot->id][$table->id] = [
                            'tableID'  => $table->id,
                            'capacity' => $table->capacity,
                            'start_time' => $slot->start_time
                        ];
                    }
                }
            }
        }

        if(count($timeTableArrays)) {
            foreach($timeTableArrays as $dateKey => $timeTableArray) {
                foreach($timeTableArray as $timeKey => $times) {
                    foreach($times as $tableKey => $time) {
                        if(isset($reservationArrays[$dateKey][$timeKey][$tableKey])) {
                            unset($timeTableArrays[$dateKey][$timeKey][$tableKey]);
                        }
                    }
                }
            }
        }

        return [$timeTableArrays, $timeSlots, $tables];
    }

    public function CheckReservation( $tableReturn, $date, $capacity, $restaurant_id, $slotId = null )
    {
        [$timeTableArrays, $timeSlots] = $this->freeCombos($date, $capacity, $restaurant_id);

        $response = [];
        if(count($timeSlots)) {
            foreach($timeSlots as $timeSlot) {
                if(!is_null($slotId) && (int) $timeSlot->id !== (int) $slotId) {
                    continue;
                }
                if(isset($timeTableArrays[$date][$timeSlot->id])) {
                    if($tableReturn) {
                        $response = $timeTableArrays[$date][$timeSlot->id];
                    } else {
                        $response[$timeSlot->id] = [
                            'id'         => $timeSlot->id,
                            'start_time' => $timeSlot->start_time,
                            'end_time'   => $timeSlot->end_time
                        ];
                    }
                }
            }
        }

        if(!blank($response)) {
            foreach($response as $k => $r) {
                if(strtotime($date) == strtotime(date('Y-m-d'))) {
                    $min  = 30;
                    $time = date('H:i:s', strtotime("+{$min} minutes"));
                    if($time > $r['start_time']) {
                        unset($response[$k]);
                    }
                }
            }
        }

        return $response;
    }

    /**
     * Every fitting table of one slot with free/booked info for green/red display.
     *
     * Each item: ['tableID','name','capacity','free','reason']
     * reason: '' (free) | 'booked' | 'passed' (slot starts within 30 min today)
     */
    public function SlotTables($date, $slotId, $capacity, $restaurant_id)
    {
        $slot = TimeSlot::where(['id' => $slotId, 'restaurant_id' => $restaurant_id, 'status' => Status::ACTIVE])->first();
        if (blank($slot)) {
            return [];
        }

        [$timeTableArrays] = $this->freeCombos($date, $capacity, $restaurant_id);
        $freeIds = isset($timeTableArrays[$date][$slot->id]) ? array_keys($timeTableArrays[$date][$slot->id]) : [];

        $slotPassed = strtotime($date) == strtotime(date('Y-m-d'))
            && date('H:i:s', strtotime('+30 minutes')) > $slot->start_time;

        $tables = Table::where(['restaurant_id' => $restaurant_id, 'status' => TableStatus::ENABLE])
            ->where('capacity', '>=', $capacity)
            ->orderBy('capacity')
            ->get();

        $response = [];
        foreach ($tables as $table) {
            $free = in_array($table->id, $freeIds) && !$slotPassed;
            $response[] = [
                'tableID'  => $table->id,
                'name'     => $table->name,
                'capacity' => $table->capacity,
                'free'     => $free,
                'reason'   => $slotPassed ? 'passed' : ($free ? '' : 'booked'),
            ];
        }

        return $response;
    }

    /**
     * Every active slot with availability info for green/red display.
     *
     * Each item: ['id','start_time','end_time','available','reason']
     * reason: '' (free) | 'booked' | 'table' (no table fits guests) | 'passed' (starts within 30 min today)
     */
    public function SlotAvailability($date, $capacity, $restaurant_id)
    {
        [$timeTableArrays, $timeSlots, $tables] = $this->freeCombos($date, $capacity, $restaurant_id);

        $response = [];
        foreach($timeSlots as $timeSlot) {
            if(isset($timeTableArrays[$date][$timeSlot->id])) {
                $response[$timeSlot->id] = [
                    'id'         => $timeSlot->id,
                    'start_time' => $timeSlot->start_time,
                    'end_time'   => $timeSlot->end_time,
                    'available'  => true,
                    'reason'     => '',
                ];
            } else {
                $response[$timeSlot->id] = [
                    'id'         => $timeSlot->id,
                    'start_time' => $timeSlot->start_time,
                    'end_time'   => $timeSlot->end_time,
                    'available'  => false,
                    'reason'     => count($tables) ? 'booked' : 'table',
                ];
            }
        }

        if(strtotime($date) == strtotime(date('Y-m-d'))) {
            $min  = 30;
            $time = date('H:i:s', strtotime("+{$min} minutes"));
            foreach($response as $k => $r) {
                if($time > $r['start_time']) {
                    $response[$k]['available'] = false;
                    $response[$k]['reason']    = 'passed';
                }
            }
        }

        return $response;
    }


}
