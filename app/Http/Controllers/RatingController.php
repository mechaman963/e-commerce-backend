<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    /**
     * Get all ratings for a specific product
     */
    public function getProductRatings($productId)
    {
        try {
            $product = Product::findOrFail($productId);
            
            $ratings = Rating::where('product_id', $productId)
                ->with('user:id,name')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'ratings' => $ratings,
                    'average_rating' => $product->average_rating,
                    'total_ratings' => $product->total_ratings
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }

    /**
     * Create or update a rating
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = Auth::id();
            
            // Check if user already rated this product
            $existingRating = Rating::where('user_id', $userId)
                ->where('product_id', $request->product_id)
                ->first();

            if ($existingRating) {
                // Update existing rating
                $existingRating->update([
                    'rating' => $request->rating,
                    'review' => $request->review
                ]);
                $rating = $existingRating;
                $message = 'Rating updated successfully';
            } else {
                // Create new rating
                $rating = Rating::create([
                    'user_id' => $userId,
                    'product_id' => $request->product_id,
                    'rating' => $request->rating,
                    'review' => $request->review
                ]);
                $message = 'Rating created successfully';
            }

            // Load user relationship
            $rating->load('user:id,name');

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $rating
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save rating'
            ], 500);
        }
    }

    /**
     * Get user's rating for a specific product
     */
    public function getUserRating($productId)
    {
        try {
            $userId = Auth::id();
            
            $rating = Rating::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();

            return response()->json([
                'success' => true,
                'data' => $rating
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user rating'
            ], 500);
        }
    }

    /**
     * Delete a rating
     */
    public function destroy($id)
    {
        try {
            $userId = Auth::id();
            
            $rating = Rating::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$rating) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rating not found or unauthorized'
                ], 404);
            }

            $rating->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rating deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete rating'
            ], 500);
        }
    }

    /**
     * Get rating statistics for a product
     */
    public function getProductRatingStats($productId)
    {
        try {
            $product = Product::findOrFail($productId);
            
            $ratingStats = Rating::where('product_id', $productId)
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->orderBy('rating', 'desc')
                ->get();

            $stats = [
                1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0
            ];

            foreach ($ratingStats as $stat) {
                $stats[$stat->rating] = $stat->count;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'average_rating' => round($product->average_rating, 1),
                    'total_ratings' => $product->total_ratings,
                    'rating_breakdown' => $stats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }
}
