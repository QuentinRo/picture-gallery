<?php

namespace App\Http\Controllers;

use App\Picture;
use App\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PictureController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Gallery $gallery)
    {
      return redirect()->route('galleries.show', $gallery);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Gallery $gallery)
    {
      $client = new \Aws\S3\S3Client([
          'version' => 'latest',
          'region' => env('AWS_DEFAULT_REGION'),
      ]);
      $bucket = env('AWS_BUCKET');

      // Set some defaults for form input fields
      $formInputs = ['acl' => 'private', 'key' => 'phi/pictures/' . Str::random(40)];

      // Construct an array of conditions for policy
      $options = [
          ['acl' => 'private'],
          ['bucket' => $bucket],
          ['starts-with', '$key', 'phi/pictures/'],
      ];

      // Optional: configure expiration time string
      $expires = '+2 hours';

      $postObject = new \Aws\S3\PostObjectV4(
          $client,
          $bucket,
          $formInputs,
          $options,
          $expires
      );

      // Get attributes to set on an HTML form, e.g., action, method, enctype
      $formAttributes = $postObject->getFormAttributes();

      // Get form input fields. This will include anything set as a form input in
      // the constructor, the provided JSON policy, your AWS access key ID, and an
      // auth signature.
      $formInputs = $postObject->getFormInputs();
      
      return view('pictures.create', compact('gallery', 'formAttributes' , 'formInputs'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Gallery $gallery, Request $request)
    {
      $picture = new Picture($request->all());
      $picture->gallery_id = $gallery->id;
      
      $picture->save();
      
      return redirect()->route('galleries.show', $gallery);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Picture  $picture
     * @return \Illuminate\Http\Response
     */
    public function show(Gallery $gallery, Picture $picture, Request $request)
    {
      if ( Str::startsWith($request->header('Accept'), 'image') ) {
        return redirect(\Storage::disk('s3')->temporaryUrl($picture->path, now()->addMinutes(1)));
      }
      else {
        return view('pictures.show', compact('gallery', 'picture'));
      }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Picture  $picture
     * @return \Illuminate\Http\Response
     */
    public function destroy(Picture $picture)
    {
        //
    }
}
