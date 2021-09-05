<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class HomeController extends Controller
{
    public function index(){
        $products = Product::all();
        return view('welcome',compact('products'));
    }

    public function addToCart(Request $request){
        $product_id = $request->input('product_id');
        $quantity     = $request->input('quantity');

        if(Cookie::get('shopping_cart'))
        {
            $cookie_data = stripslashes(Cookie::get('shopping_cart'));
            $cart_data = json_decode($cookie_data, true);
        }
        else
        {
            $cart_data = array();
        }
        //If product already in cart
        $itemlist = array_column($cart_data, 'item_id');
        $prod_id_is_there = $product_id;

        if(in_array($prod_id_is_there, $itemlist))
        {
            foreach($cart_data as $keys => $values)
            {
                if($cart_data[$keys]["item_id"] == $product_id)
                {
                    $cart_data[$keys]["item_quantity"] = $request->input('quantity');
                    $item_data = json_encode($cart_data);
                    $minutes = 60;
                    Cookie::queue(Cookie::make('shopping_cart', $item_data, $minutes));
                    $response['already'] ='"'.$cart_data[$keys]["item_name"].'" Already in Cart';
                    return response()->json($response);

                }
            }
        }




        $products = Product::find($product_id);
        $prod_name = $products->name;
        $prod_image = $products->image;
        $priceval = $products->price;

        if($products)
        {
            $item_array = array(
                'item_id' => $product_id,
                'item_name' => $prod_name,
                'item_quantity' => $quantity,
                'item_price' => $priceval,
                'item_image' => $prod_image,
                'sub_total' => $priceval*$quantity
            );
            $cart_data[] = $item_array;

            $item_data = json_encode($cart_data);
            $minutes = 60;
            Cookie::queue(Cookie::make('shopping_cart', $item_data, $minutes));
            $response['status'] ='"'.$prod_name.'" Added to Cart';
            return response()->json($response);
        }
    }

    public function cartloadbyajax()
    {
        if(Cookie::get('shopping_cart'))
        {
            $cookie_data = stripslashes(Cookie::get('shopping_cart'));

            $cart_data = json_decode($cookie_data, true);
            //fetch all item

            $totalcart = count($cart_data);
            $response['totalcart'] = $totalcart;
            $response['cart-list'] = $cart_data;
            echo json_encode($response); die;
            return;
        }
        else
        {
            $totalcart = "0";
            echo json_encode(array('totalcart' => $totalcart)); die;
            return;
        }
    }

}
