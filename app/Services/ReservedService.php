<?php

namespace App\Services;

use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReservedService
{
    public function reservedBlockService($request)
    {
        $secretKeyGenerate = Str::random(12);
        $deactivate = Carbon::now()->addDays($request->days)->toDateTimeString();
        $location = Location::query()->find($request->location_id);
        $emptyBlocks = DB::select("SELECT (loc.quantity_blocks - SUM(reserved_blocs)) as empty_blocks FROM laravel.location_user loc_u
                                            INNER JOIN locations loc ON loc.id = loc_u.location_id
                                            WHERE location_id=$request->location_id GROUP BY location_id");

        if (!empty($request->location_id) && !empty($request->quantity_blocks) && !empty($request->days) && !empty($request->user_id)) {
            $response = DB::select("SELECT loc.fee,(loc.quantity_blocks - SUM(loc_u.reserved_blocs)) as loose_blocks,SUM(loc_u.reserved_blocs) as reserved_blocs, loc.quantity_blocks FROM laravel.locations loc
                                            INNER JOIN laravel.location_user loc_u ON loc.id = loc_u.location_id
                                            WHERE loc.id = $request->location_id AND loc_u.status = 1
                                            GROUP BY loc_u.location_id");

            if (empty($response)) {
                $requiredMoney = $request->quantity_blocks * $location->fee * $request->days;
                if (empty($request->money) || $requiredMoney != $request->money) {
                    return \response()->json(['message' => [
                        'money' => $requiredMoney,
                        'error' => 'Add money to request or check the invoice amount'
                    ]]);
                }

                User::query()
                    ->find($request->user_id)
                    ->locations()->attach($location, [
                        'reserved_blocs' => $request->quantity_blocks,
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'deactivate' => $deactivate,
                        'secret_key' => $secretKeyGenerate,
                        'money' => $requiredMoney
                    ]);

                return response()->json([
                    'quantity_blocks' => $request->quantity_blocks,
                    'location' => $location->name . ' / ' . $location->name_eng,
                    'secret_key' => $secretKeyGenerate,
                    'money' => $requiredMoney,
                    'deactivate' => $deactivate,
                    'status' => 'The operation was successful:)',
                    'warning' => 'Don\'t Lose Your Secret Key'
                ], Response::HTTP_OK);
            } else {
                if ($response[0]->loose_blocks < $request->quantity_blocks) {
                    return \response()->json(['message' => 'Unavailable number of blocks. Available - ' . $emptyBlocks[0]->empty_blocks], Response::HTTP_BAD_REQUEST);
                }
            }

            $requiredMoney = $request->quantity_blocks * $response[0]->fee * $request->days;

            if (empty($request->money) || $requiredMoney != $request->money) {
                return \response()->json(['message' => [
                    'money' => $requiredMoney,
                    'error' => 'Add money to request or check the invoice amount'
                ]]);
            }

            User::query()
                ->find($request->user_id)
                ->locations()->attach($location, [
                    'reserved_blocs' => $request->quantity_blocks,
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'deactivate' => $deactivate,
                    'secret_key' => $secretKeyGenerate,
                    'money' => $requiredMoney
                ]);

            return response()->json([
                'quantity_blocks' => $request->quantity_blocks,
                'location' => $location->name . ' / ' . $location->name_eng,
                'secret_key' => $secretKeyGenerate,
                'money' => $requiredMoney,
                'deactivate' => $deactivate,
                'status' => 'The operation was successful:)',
                'warning' => 'Don\'t Lose Your Secret Key'
            ], Response::HTTP_OK);
        }
        return response()->json([], Response::HTTP_BAD_REQUEST);
    }

    public function reservedHistory(int $id)
    {
        $result = DB::select("SELECT l.name_eng,if(lu.status = 0, 'overdue','active') as status,lu.money,lu.secret_key,lu.reserved_blocs,
                                    lu.created_at,lu.deactivate FROM location_user lu
                                    INNER JOIN locations l ON lu.location_id = l.id WHERE user_id = $id");

        return $result;
    }
}
