<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Support\Facades\File;

class UploadImageController extends Controller
{
    public function uploadeImage($img){
        $imageName = time().'.'.$img->getClientOriginalName().'-'.$img->getClientOriginalExtension();
        $img->move(public_path('logo'), $imageName);
        return asset('/logo/' . $imageName);
    }
    public function uploadMultiImages($images){
        $img=array();
        foreach($images as $file){
            $name= time().'.'.$file->getClientOriginalName().'-'.$file->getClientOriginalExtension();
            $file->move(public_path('images'),$name);
            $img[]=(new Image(['img'=>asset('/images/' . $name)]));
        }
        return $img;
    }
    public function deleteLogoImage($img){
        if($img!="no image"){
            $image_path =explode("/logo/",$img);
            $imgDel=public_path("/logo/".$image_path[1]);
            if(File::exists($imgDel)) {
                File::delete($imgDel);
            }
        }
    }
    public function deleteMultiImage($imgs){
        foreach($imgs as $img)    
            if($img->img!="no image"){
                $image_path =explode("/images/",$img->img);
                $imgDel=public_path("/images/".$image_path[1]);
                if(File::exists($imgDel)) {
                    File::delete($imgDel);
                }
                $img->delete();
            }
    }
    public function deleteMultiImagePaths($imgs){
        foreach($imgs as $img)    
            if($img!="no image"){
                $image_path =explode("/images/",$img->img);
                $imgDel=public_path("/images/".$image_path[1]);
                if(File::exists($imgDel)) {
                    File::delete($imgDel);
                }
            }
    }
}