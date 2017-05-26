<?php

namespace App\Http\Controllers;

use App\User;
use App\ProfileImages;
use Validator;
use App\Http\Requests;
use Illuminate\Http\Request;
use Input;
use Intervention\Image\Facades\Image;

class ImageController extends Controller
{
    /**-
     * Images of new user.
     * 
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        return view('img_up');
    }   


    public function upload(Request $request)
    {
        $files = Input::file('userfile');
        if(!empty($files))
        {
            $json = array(
                'files' => array()
                );

            $filename = $files->getClientOriginalName();
            $pid = $request->gpid;
            $image_data             = new ProfileImages;
            $image_data->profile_id = $pid;
            $image_data->image      = $pid."_".$filename;
            $image_data->save();
            $img_id = $image_data->id;

            $json['files'][] = array(
                'name' => $pid."_".$filename,
                'size' => $files->getSize(),
                'type' => $files->getMimeType(),
                'url' => public_path().'/uploads/files/'.$pid."_".$filename,
                'thumbnailUrl' => public_path().'/uploads/thumbnails/'.$pid."_".$filename,
                'deleteType' => 'DELETE',
                'deleteUrl' => public_path().'/delete_img/'.$pid."_".$filename,
                'id' => $pid,
                );
            Image::make($files->getRealPath())->resize(120, 80)->save('uploads/thumbnails/'.$pid."_".$filename);
            $upload = $files->move( public_path().'/uploads/files', $pid."_".$filename );

            return response()->json($json);
        }

    }    

    public function delete_img($id)
    {
        $success = Image::make(public_path().'/uploads/thumbnails/'.$id)->destroy();

        $info = array();
        $info['sucess'] = $success;
        $info['path'] = public_path().'/uploads/files/'.$id;
        $info['file'] = is_file(public_path().'/uploads/files/'.$id);

        return response()->json($info);
    }


}
