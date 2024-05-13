<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\User;
use Carbon\Carbon;
use Faker\Provider\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

use function PHPSTORM_META\map;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $wrapper = collect([]);

            if ($request->has('uuid')) {
                $user_uuid = $request->input('uuid');
                $orders = Order::where("user_uuid",  $user_uuid)->get()->toArray();
            } else {
                $orders = Order::get()->toArray();

                foreach ($orders as $key => $order) {

                    $existParent = $wrapper->contains($order["parent_uuid"]);

                    if (!$existParent) {
                        $wrapper->push(["parent_uuid" => $order["parent_uuid"], "user_uuid" => $order["user_uuid"], "detail" => []]);
                    }

                    // $orIndex = 0;

                    // foreach ($wrapper as $key => $value) {
                    //     if ($value["parent_uuid"] == $order["parent_uuid"]) {
                    //         $orIndex = $key;
                    //     }
                    // }

                    $wrapper = $wrapper->map(function ($item, $i) use ($order) {
                        array_push($item["detail"], $order);

                        return $item;
                    });
                }
            }

            $wrapper = $wrapper->map(function ($item, $i) {
                $payment = Payment::select("paid")->where("parent_uuid", $item["parent_uuid"])->first();

                $item["payment"] = $payment;

                $user = User::select("name")->where("uuid", $item["user_uuid"])->first();

                $item["user"] = $user;

                $item["detail"] = collect($item["detail"])->map(function ($dtl, $idtl) {
                    $product = Product::select("name")->where("uuid", $dtl["product_uuid"])->first();

                    $dtl["product"] = $product;

                    return $dtl;
                });

                return $item;
            });

            return response()->json([
                "success" => true,
                "message" => "Show list orders success",
                "data" => $wrapper
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "data" => ["error" => $e->getMessage()]
            ], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $parent_uuid = Uuid::uuid();
        $orders = $request->get("orders");
        $user_uuid = $request->get("user")["uuid"];
        $shipping_uuid = $request->get("shipping")["uuid"];
        $payment = $request->get("payment");

        try {
            foreach ($orders as $key => $value) {
                $data = [
                    "uuid" => Uuid::uuid(),
                    "user_uuid" => $user_uuid,
                    "parent_uuid" => $parent_uuid,
                    "product_uuid" => $value["detail"]["uuid"],
                    "note" => $value["detail"]["note"],
                    "total" =>  $value["detail"]["total"],
                    "price" =>  $value["detail"]["price"],
                    "order_date" => Carbon::now()
                ];

                Order::create($data);
            }

            $paymentData = [
                "uuid" => Uuid::uuid(),
                "parent_uuid" => $parent_uuid,
                "shipping_uuid" => $shipping_uuid,
                "method" => $payment["method"],
                "provider" => $payment["provider"],
                "total" => $payment["priceTotal"],
            ];

            Payment::create($paymentData);

            Shipping::where("uuid", $shipping_uuid)->where("user_uuid", $user_uuid)->update(["status" => "Y"]);

            Shipping::where("uuid", "!=", $shipping_uuid)->where("user_uuid", $user_uuid)->update(["status" => "N"]);

            return response()->json([
                "success" => true,
                "message" => "Order success",
                "data" => [
                    "order" => [
                        "parent_uuid" => $parent_uuid
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "data" => ["error" => $e]
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
