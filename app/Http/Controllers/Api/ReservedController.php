<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\ReservedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;


/**
 * @OA\Info(title="My First API", version="0.1")
 *
 * Class ReservedController
 * @package App\Http\Controllers\Api
 */
class ReservedController extends Controller
{

    /**
     * @var ReservedService
     */
    private $reservedService;

    public function __construct(ReservedService $reservedService)
    {
        //
        $this->reservedService = $reservedService;
    }

    /**
     * @OA\Get(
     ** path="/api/reserved",
     *   tags={"Reserved"},
     *   summary="index",
     *   operationId="index",
     *
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="user_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *)
     **/

    public function index(Request $request): JsonResponse
    {
        if ($request->user_id && is_numeric($request->user_id)) {
            $response = DB::select("SELECT SUM(lu.reserved_blocs) as reserved_blocs,lu.location_id,l.name as location_name,
                                        l.name_eng as location_name_eng FROM laravel.users as u
                                        INNER JOIN laravel.location_user as lu on lu.user_id = u.id
                                        INNER JOIN laravel.locations as l on l.id = lu.location_id
                                        WHERE lu.user_id = $request->user_id AND lu.status = 1
                                        GROUP BY lu.location_id ORDER BY lu.location_id");
            return response()->json($response);
        }
        $response = DB::select("SELECT (l.quantity_blocks - SUM(lu.reserved_blocs)) as loose_blocks,SUM(lu.reserved_blocs) as reserved_blocs,
                                        lu.location_id,l.name as location_name,
                                        l.name_eng as location_name_eng FROM laravel.users as u
                                        INNER JOIN laravel.location_user as lu on lu.user_id = u.id
                                        INNER JOIN laravel.locations as l on l.id = lu.location_id
                                        WHERE lu.status = 1
                                        GROUP BY lu.location_id ORDER BY lu.location_id");

        return response()->json($response);
    }

    /**
     * @OA\Get(
     ** path="/api/reserved/block",
     *   tags={"Reserved"},
     *   summary="Reserved",
     *   operationId="Reserved",
     *
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="user_id",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="location_id",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="days",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="quantity_blocks",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="money",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *)
     **/

    public function reservedBlock(Request $request)
    {
        return $this->reservedService->reservedBlockService($request);
    }

    /**
     * @OA\Get(
     *  path="/api/reserved/my-bookings",
     *   tags={"Reserved"},
     *   summary="History",
     *   operationId="History",
     *
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Parameter(
     *     name="user_id",
     *     in="query",
     *     required=true,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *     )
     * )
     */
    public function reservedHistory(Request $request)
    {
        return $this->reservedService->reservedHistory($request->user_id);
    }
}
