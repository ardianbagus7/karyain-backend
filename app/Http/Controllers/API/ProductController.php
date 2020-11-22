<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $user_id = $request->input('user_id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $category = $request->input('category');
        $target_end = $request->input('target_end');
        $total_donation = $request->input('total_donation');

        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');


        if ($id) {
            $product = Product::find($id);
            $product->total_donation = $product->countTransactionsByProduct($product->id);
            $product->donator = $product->getAllSuccessTransactionByProduct($product->id);
            $product->url_photo = $product->getUrlPhotoAttribute($product->photo_path);
            $product->user = $product->user;

            if ($product) {
                return ResponseFormatter::success(
                    $product,
                    'Data produk berhasil diambil',
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    'Data produk tidak ada',
                    404
                );
            }
        }

        $product = Product::query();

        if ($user_id) {
            $product->where('user_id', $user_id);
        }

        if ($name) {
            $product->where('name', 'like', '%' . $name . '%');
        }

        if ($category) {
            $product->where('category', 'like', '%' . $category . '%');
        }

        if ($price_from) {
            $product->where('target_funding', '>=', $price_from);
        }

        if ($price_to) {
            $product->where('target_funding', '<=', $price_from);
        }

        $products = array();
        foreach ($product->latest()->get() as $item) {
            $item->total_donation = $item->countTransactionsByProduct($item->id);
            $item->donator =  $item->getAllSuccessTransactionByProduct($item->id);
            $item->user = $item->user;
            array_push($products, $item);
        }

        return ResponseFormatter::success(
            collect($products)->paginate($limit),
            'Data list produk berhasil diambil'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => 'required',
            'target_funding' => 'required',
            'target_end' => 'required',
        ]);

        try {

            $product = Product::create([
                'user_id' => Auth::user()->id,
                'name' => $request->name,
                'description' => $request->description,
                'category' => $request->category,
                'target_funding' => $request->target_funding,
                'target_end' => $request->target_end,
                'video_path' => $request->video_path,
            ]);

            // image 
            $validator = Validator::make($request->all(), [
                'file' => 'required|image|max:2048'
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'error' => $validator->errors()
                ], 'Update photo fails', 401);
            }

            if ($request->file('file')) {
                $file = $request->file->store('assets/user', 'public');

                // Simpan foto url ke database
                $product->photo_path = $file;
                $product->save();
            }

            return ResponseFormatter::success([
                'product' => $product
            ]);
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e,
            ], 'Authentication Failed', 500);
        }
    }
}
