<?php

namespace App\Http\Controllers\Api\Public;

use App\Models\Education;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\EducationResource;

class EducationController extends Controller
{
    public function index()
    {
        $posts = Education::with('user', 'category')->latest()->paginate(10);

        //return with Api Resource
        return new EducationResource(true, 'List Data Edukasi', $posts);
    }

    public function show($slug)
    {
        $post = Education::with('user', 'category')->where('slug', $slug)->first();

        if ($post) {
            //return with Api Resource
            return new EducationResource(true, 'Detail Data Edukasi', $post);
        }

        //return with Api Resource
        return new EducationResource(false, 'Detail Data Edukasi Tidak Ditemukan!', null);
    }

    public function homePage()
    {
        $posts = Education::with('user', 'category')->latest()->take(6)->get();

        //return with Api Resource
        return new EducationResource(true, 'List Data Edukasi HomePage', $posts);
    }
}
