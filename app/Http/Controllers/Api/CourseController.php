<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreArticlesRequest;
use App\Http\Requests\Dashboard\UpdateArticlesRequest;
use App\Http\Resources\Api\ArticleResources;
use App\Http\Resources\Api\CourseResources;
use App\Http\Resources\Api\VideoResources;
use App\Models\ArticalComment;
use App\Models\Articles;
use App\Models\Category;
use App\Models\Course;
use App\Models\Video;
use Auth;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         // Paginate articles with related comments and vendors
         $courses = Course::where('open','1')->paginate(6);
        // Transform the latest blogs
        $transformedLatestCourses = CourseResources::collection($courses);

        return $this->success(data:$transformedLatestCourses);

    }

    public function lectures($id)
    {
        // Fetch the course with its videos
        $course = Course::with('videos')->find($id);

        // Check if the course exists
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }


        return response()->json(['message' => 'Course updated successfully!',[
            'course' => $course->name,
            'videos' => VideoResources::collection($course->videos)
        ]], 200);

    }

    public function getNextAndPreviousVideo($video_id)
{
    $currentVideo = Video::find($video_id);

    if (!$currentVideo) {
        return response()->json(['message' => 'Video not found'], 404);
    }

    $section_id = $currentVideo->section_id;

    // Get previous and next videos within the same section
    $previousVideo = Video::where('section_id', $section_id)
        ->where('id', '<', $video_id)
        ->orderBy('id', 'desc')
        ->first();

    $nextVideo = Video::where('section_id', $section_id)
        ->where('id', '>', $video_id)
        ->orderBy('id', 'asc')
        ->first();

    return response()->json([
        'current' => $currentVideo,
        'previous' => $previousVideo,
        'next' => $nextVideo
    ]);
}


    public function single($id)
    {
        // Retrieve the single article with its related comments and vendor data
        $course = Course::where('id', $id)
             ->first();

        // If the article does not exist, return a 404 response
        if (!$course) {
            return $this->failure("Article not found");

        }

        // Use the singular ArticleResource to transform the single article
        $data = CourseResources::make($course); // Use make() to transform a single resource

        // Return the transformed article data
        return $this->success(
            message: "Single course",
            data: $data
        );
    }







     public function createCommentes(Request $request)
     {
         $data=$request->validate([
            'article_id' => 'required|exists:articles,id',
            'comment' => 'required|string|max:500',
        ]);
        if(Auth::guard('vendor')->user()){


            if(Auth::guard('vendor')->user()->status != 2){
            if(Auth::guard('vendor')->user()->status==1){
                  return $this->validationFailure(errors:['message'=>__('this account is pending please wait for admin approval')]);
            }
            elseif(Auth::guard('vendor')->user()->status==0){
                return $this->validationFailure(errors:['message'=>__('this account is blocekd please contact with admin')]);
            }
            elseif(Auth::guard('vendor')->user()->status==3){
                return $this->validationFailure(errors:['message'=>__('this account is rejected please create new account or contact with admin')]);
            }

        }
        else{
            $comment=ArticalComment::create([
                'vendor_id' => Auth::guard('vendor')->user()->id,
                'article_id' => $data['article_id'],
                'description_ar' => request('comment'),
                'description_en' => request('comment'),

                ]);
            return $this->success(data:$comment);
      }
    } else{
        return $this->validationFailure(errors:['message'=>__('must be login to create comment')]);

    }
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Articles  $articles
     * @return \Illuminate\Http\Response
     */
    public function show(Articles $article)
    {
        $this->authorize('show_articles');
        return view('dashboard.articles.show',compact('article'));
    }

    public function edit(Articles $article)
    {
        // Ensure the user has permission to update articles
        $this->authorize('update_articles');

        // Fetch categories with the dynamic name based on the current locale
        // Explicitly reference 'categories.id' to avoid ambiguity
        $categories = Category::select('categories.id', 'name_' . app()->getLocale())
                              ->join('article_category', 'categories.id', '=', 'article_category.category_id')
                              ->get();

        // Get selected category IDs for the article
        // Explicitly reference 'categories.id' here too
        $selectedCategoriesIds = $article->categories()->pluck('categories.id')->toArray();

        // Return the view with article, categories, and selected category IDs
        return view('dashboard.articles.edit', compact('article', 'categories', 'selectedCategoriesIds'));
    }

    public function update(UpdateArticlesRequest $request, Articles $article)
    {
        $this->authorize('update_articles');

        // Get validated data from the request
        $data = $request->validated();
        unset($data['category_id']); // Remove category_id from $data
        // Check if the 'main_image' file is provided and update the image
        if ($request->hasFile('main_image')) {
            // Delete the existing image first if it exists
            deleteImage($article->main_image, 'articles');
            // Upload the new image
            $data['main_image'] = uploadImage($request->file('main_image'), 'articles');
        }
        $data['assign_to']=Auth::user()->id;

        // Update the article data (excluding category_id)
        $article->update($data);

        // Sync the categories using the category_id from the request
        if ($request->has('category_id') && is_array($request->category_id)) {
            // Sync the categories in the pivot table
            $article->categories()->sync($request->category_id);
        }

        // Optionally, return a success response or redirect
     }



    public function destroy(Request $request,Articles $article)
    {
        $this->authorize('delete_articles');

        if($request->ajax())
        {
            $article->delete();
            deleteImage($article->main_image , 'articles' );
        }
    }
}
