<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Education;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Resources\EducationResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EducationController extends Controller
{
    public function index()
    {
        $posts = Education::with('user', 'category')->when(request()->search, function ($posts) {
            $posts = $posts->where('title', 'like', '%' . request()->search . '%');
        })->where('user_id', auth()->user()->id)->latest()->paginate(5);

        //append query string to pagination links
        $posts->appends(['search' => request()->search]);

        //return with Api Resource
        return new EducationResource(true, 'List Data Edukasi', $posts);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image'         => 'required|image|mimes:jpeg,jpg,png|max:2000',
            'title'         => 'required|unique:posts',
            'category_id'   => 'required',
            'content'       => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        $post = Education::create([
            'image'       => $image->hashName(),
            'title'       => $request->title,
            'slug'        => Str::slug($request->title, '-'),
            'category_id' => $request->category_id,
            'user_id'     => auth()->guard('api')->user()->id,
            'content'     => $request->content
        ]);


        if ($post) {
            //return success with Api Resource
            return new EducationResource(true, 'Data Edukasi Berhasil Disimpan!', $post);
        }

        //return failed with Api Resource
        return new EducationResource(false, 'Data Edukasi Gagal Disimpan!', null);
    }

    public function show($id)
    {
        $post = Education::with('category')->whereId($id)->first();

        if ($post) {
            //return success with Api Resource
            return new EducationResource(true, 'Detail Data Edukasi!', $post);
        }

        //return failed with Api Resource
        return new EducationResource(false, 'Detail Data Edukasi Tidak DItemukan!', null);
    }

    public function update(Request $request, Education $education)
    {
        $validator = Validator::make($request->all(), [
            'title'         => 'required|unique:education,title,' . $education->id,
            'category_id'   => 'required',
            'content'       => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //check image update
        if ($request->file('image')) {

            //remove old image
            Storage::disk('local')->delete('public/posts/' . basename($education->image));

            //upload new image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            $education->update([
                'image'       => $image->hashName(),
                'title'       => $request->title,
                'slug'        => Str::slug($request->title, '-'),
                'category_id' => $request->category_id,
                'user_id'     => auth()->guard('api')->user()->id,
                'content'     => $request->content
            ]);
        }

        $education->update([
            'title'       => $request->title,
            'slug'        => Str::slug($request->title, '-'),
            'category_id' => $request->category_id,
            'user_id'     => auth()->guard('api')->user()->id,
            'content'     => $request->content
        ]);

        if ($education) {
            //return success with Api Resource
            return new EducationResource(true, 'Data Edukasi Berhasil Diupdate!', $education);
        }

        //return failed with Api Resource
        return new EducationResource(false, 'Data Edukasi Gagal Disupdate!', null);
    }

    public function destroy(Education $education)
    {
        //remove image
        Storage::disk('local')->delete('public/posts/' . basename($education->image));

        if ($education->delete()) {
            //return success with Api Resource
            return new EducationResource(true, 'Data Edukasi Berhasil Dihapus!', null);
        }

        //return failed with Api Resource
        return new EducationResource(false, 'Data Edukasi Gagal Dihapus!', null);
    }
}
