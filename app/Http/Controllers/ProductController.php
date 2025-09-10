<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allProducts = Product::with(['Images', 'ratings'])->get();
        $products = Product::with(['Images', 'ratings'])->where('status', '=', 'published')->paginate($request->input('limit', 10));
        $finalResult = $request->input('limit') ? $products : $allProducts;
        
        // Add calculated rating fields to each product
        $finalResult->transform(function ($product) {
            $product->average_rating = $product->average_rating;
            $product->total_ratings = $product->total_ratings;
            return $product;
        });
        
        return $finalResult;
    }

    public function getLastSaleProducts(Request $request)
    {
        $products = Product::with(['Images', 'ratings'])
            ->where('status', '=', 'published')
            ->where('discount', '>', '0')
            ->latest()
            ->take(5)
            ->get();
        
        // Add calculated rating fields to each product
        $products->transform(function ($product) {
            $product->average_rating = $product->average_rating;
            $product->total_ratings = $product->total_ratings;
            return $product;
        });
        
        return $products;
    }


    public function getLatest(Request $request)
    {
        $products = Product::with(['Images', 'ratings'])->where('status', '=', 'published')->latest()->take(6)->get();
        
        // Add calculated rating fields to each product
        $products->transform(function ($product) {
            $product->average_rating = $product->average_rating;
            $product->total_ratings = $product->total_ratings;
            return $product;
        });
        
        return $products;
    }

    public function getTopRated(Request $request)
    {
        // Get products with calculated average ratings
        $products = Product::with(['Images', 'ratings'])
            ->where('status', '=', 'published')
            ->get()
            ->map(function ($product) {
                $product->calculated_rating = $product->average_rating;
                return $product;
            })
            ->sortByDesc('calculated_rating')
            ->take(10)
            ->values();
        
        // If no products with ratings found, get latest published products as fallback
        if ($products->isEmpty() || $products->every(fn($p) => $p->calculated_rating == 0)) {
            $products = Product::with(['Images', 'ratings'])
                ->where('status', '=', 'published')
                ->latest()
                ->take(10)
                ->get();
        }
        
        return $products;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            \Log::info('Product creation request received');
            \Log::info('Request data: ' . json_encode($request->all()));
            \Log::info('Files in request: ' . json_encode($request->allFiles()));
            
            $product = new Product();
            $request->validate([
                'title' => 'required',
                'description' => 'required',
                'price' => 'required | numeric',
                'discount' => 'required | numeric',
                'About' => 'required'
            ]);
            
            $productCreated = $product->create([
                'category' => $request->category,
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'About' => $request->About,
                'discount' => $request->discount,
            ]);
            
            $productCreated->status = 'published';
            $productCreated->save();
            
            \Log::info('Product created with ID: ' . $productCreated->id);
            
            $uploadedImages = [];
            
            if ($request->hasFile('images')) {
                $productId = $productCreated->id;
                $files = $request->file("images");
                $i = 0;
                
                \Log::info('Processing images for product ID: ' . $productId);
                
                // Handle both single file and multiple files
                if (is_array($files)) {
                    \Log::info('Multiple files received: ' . count($files));
                    foreach ($files as $file) {
                        $i = $i + 1;
                        $image = new ProductImage();
                        $image->product_id = $productId;
                        $filename = date('YmdHis') . $i . '.' . $file->getClientOriginalExtension();
                        $path = 'images';
                        
                        \Log::info('Saving file: ' . $filename . ' to path: ' . $path);
                        
                        $file->move($path, $filename);
                        $image->image = url('/') . '/images/' . $filename;
                        $image->save();
                        $uploadedImages[] = $image->image;
                        
                        \Log::info('Image saved successfully: ' . $image->image);
                    }
                } else {
                    // Single file
                    \Log::info('Single file received');
                    $file = $files;
                    $i = 1;
                    $image = new ProductImage();
                    $image->product_id = $productId;
                    $filename = date('YmdHis') . $i . '.' . $file->getClientOriginalExtension();
                    $path = 'images';
                    
                    \Log::info('Saving file: ' . $filename . ' to path: ' . $path);
                    
                    $file->move($path, $filename);
                    $image->image = url('/') . '/images/' . $filename;
                    $image->save();
                    $uploadedImages[] = $image->image;
                    
                    \Log::info('Image saved successfully: ' . $image->image);
                }
            } else {
                \Log::info('No images found in request');
            }
            
            return response()->json([
                'message' => 'Product created successfully',
                'product' => $productCreated,
                'images' => $uploadedImages
            ], 200);
            
        } catch (\Exception $e) {
            \Log::error('Error creating product: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error creating product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::with(['Images', 'ratings'])->where('id', $id)->first();
        
        if ($product) {
            $product->average_rating = $product->average_rating;
            $product->total_ratings = $product->total_ratings;
        }
        
        return $product ? [$product] : [];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $request->validate([
            'category' => 'required',
            'title' => 'required',
            'description' => 'required',
            'price' => 'required | numeric',
            'discount' => 'required | numeric',
            'About' => 'required'
        ]);
        $product->update([
            'category' => $request->category,
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'About' => $request->About,
            'discount' => $request->discount,

        ]);
        $product->status = 'published';
        $product->save();
        $productId = $product->id;
        $uploadedImages = [];
        
        if ($request->hasFile('images')) {
            $files = $request->file("images");
            $i = 0;
            
            // Handle both single file and multiple files
            if (is_array($files)) {
                foreach ($files as $file) {
                    $i = $i + 1;
                    $image = new ProductImage();
                    $image->product_id = $productId;
                    $filename = date('YmdHis') . $i . '.' . $file->getClientOriginalExtension();
                    $path = 'images';
                    $file->move($path, $filename);
                    $image->image = url('/') . '/images/' . $filename;
                    $image->save();
                    $uploadedImages[] = $image->image;
                }
            } else {
                // Single file
                $file = $files;
                $i = 1;
                $image = new ProductImage();
                $image->product_id = $productId;
                $filename = date('YmdHis') . $i . '.' . $file->getClientOriginalExtension();
                $path = 'images';
                $file->move($path, $filename);
                $image->image = url('/') . '/images/' . $filename;
                $image->save();
                $uploadedImages[] = $image->image;
            }
        }
        
        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
            'images' => $uploadedImages
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $productImages = ProductImage::where('product_id', '=', $id)->get();
        foreach ($productImages as $productImage) {
            $path = public_path() . '/images/' . substr($productImage['image'], strrpos($productImage['image'], '/') + 1);
            if (File::exists($path)) {
                File::delete($path);
            }
        }
        DB::table('products')->where('id', '=', $id)->delete();
    }
}
