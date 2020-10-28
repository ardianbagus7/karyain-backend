<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $category = $request->input('category');
        $target_end = $request->input('target_end');

        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');


        if ($id) {
            $product = Product::find($id);
            $product->total_donation = $product->countTransactionsByProduct($product->id);
            $product->donator = $product->getAllSuccessTransactionByProduct($product->id);

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

        $product = Product::all()->map(function ($item, $key) {
            $_item = $item->getAttributes();
            $_item['total_donation'] =
                $item->countTransactionsByProduct($item->id);
            $_item['donator'] = $item->getAllSuccessTransactionByProduct($item->id);
            return $_item;
        });

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

        // $product->url_photo_path = $product->getPicturePathAttribute();
        return ResponseFormatter::success(
            $product->paginate($limit),
            'Data list produk berhasil diambil'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => 'required',
            'category' => 'required',
            'target_funding' => 'required',
            'target_end' => 'required',
            'video_path' => 'required',
        ]);

        try {

            $product = Product::create([
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
