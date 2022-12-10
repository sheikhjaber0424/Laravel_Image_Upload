<?php

namespace App\Http\Controllers;

use App\Models\Bucket;
use App\Models\PrivateImg;
use App\Models\PublicImg;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MainController extends Controller
{
    public function public_index()
    {
        $items = PublicImg::all();
        return view('public_image.index', compact('items'));
    }


    public function public_create()
    {
        return view('public_image.create');
    }


    public function public_store(Request $request)
    {
        $valid = $request->validate([

            'title' => ['required', 'string', 'max:255'],
            'image' => ['required', 'mimes:png,jpg,jpeg', 'max:2048'],
        ]);


        if ($request->hasFile('image'))
            $valid['image'] = $request->file('image')->store('uploads/img', 'public');

        if (PublicImg::create($valid))
            return redirect()->route('public.index');
    }
    public function public_edit(PublicImg $item)
    {
        return view('public_image.edit', compact('item'));
    }

    public function public_update(Request $request, PublicImg $item)
    {

        $valid = $request->validate([

            'title' => ['required', 'string', 'max:255'],
            'image' => ['required', 'mimes:png,jpg,jpeg', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if (Storage::disk('public')->exists($item->getRawOriginal('image')))
                Storage::disk('public')->delete($item->getRawOriginal('image'));
            $valid['image'] = $request->file('image')->store('uploads/img', 'public');
        }

        if ($item->update($valid))
            return redirect()->route('public.index');
    }


    public function public_delete(PublicImg $item)
    {
        if (Storage::disk('public')->exists($item->getRawOriginal('image')))
            Storage::disk('public')->delete($item->getRawOriginal('image'));

        $item->delete();

        return redirect()->route('public.index');
    }














    public function private_index()
    {
        $private_items = PrivateImg::all();
        return view('private_image.index', compact('private_items'));
    }

    public function private_create()
    {
        return view('private_image.create');
    }



    public function private_store(Request $request)
    {
        $valid = $request->validate([

            'title' => ['required', 'string', 'max:255'],
            'image' => ['required', 'image', 'max:2048'],
        ]);


        if ($request->hasFile('image'))
            $valid['image'] = $request->file('image')->store('uploads/img2');

        if (PrivateImg::create($valid))
            return redirect()->route('private.index');
    }


    public function private_show_image(PrivateImg $item)
    {
        return response()->file(Storage::path($item->image));
    }


    public function private_edit(PrivateImg $item)
    {
        return view('private_image.edit', compact('item'));
    }



    public function private_update(Request $request, PrivateImg $item)
    {

        $valid = $request->validate([

            'title' => ['required', 'string', 'max:255'],
            'image' => ['required', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if (Storage::exists($item->image))
                Storage::delete($item->image);
            $valid['image'] = $request->file('image')->store('uploads/img2');
        }

        if ($item->update($valid))
            return redirect()->route('private.index');
    }



    public function private_delete(PrivateImg $item)
    {
        if (Storage::exists($item->image))
            Storage::delete($item->image);
        $item->delete();

        return redirect()->route('private.index');
    }







    public function s3_index()
    {
        $s3_items = Bucket::all();
        return view('S3_Bucket.index', compact('s3_items'));
    }


    public function s3_create()
    {

        return view('S3_Bucket.create');
    }


    public function s3_store(Request $request)
    {
        $valid = $request->validate([

            'title' => ['required', 'string', 'max:255'],
            'image' => ['required', 'image', 'max:2048'],
        ]);


        if ($request->hasFile('image')) {
            try {
                $valid['image'] = $request->file('image')->storePublicly('uploads/bucket', 's3');
            } catch (\Exception $exception) {
                return back()->with('error', $exception->getMessage());
            }
        }
        if (Bucket::create($valid))
            return redirect()->route('private.index');
    }


    public function s3_edit(Bucket $item)
    {
        return view('S3_Bucket.edit', compact('item'));
    }

    public function s3_update(Request $request, Bucket $item)
    {

        $valid = $request->validate([

            'title' => ['required', 'string', 'max:255'],
            'image' => ['required', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            try {
                if (Storage::disk('s3')->exists($item->getRawOriginal('image')))
                    Storage::disk('s3')->delete($item->getRawOriginal('image'));

                $valid['image'] = $request->file('image')->storePublicly('uploads/bucket', 's3');
            } catch (\Exception $exception) {
                return back()->with('error', $exception->getMessage());
            }
        }

        if ($item->update($valid))
            return redirect()->route('s3.index');
    }


    public function s3_delete(Bucket $item)
    {
        if (Storage::disk('s3')->exists($item->getRawOriginal('image')))
            Storage::disk('s3')->delete($item->getRawOriginal('image'));

        $item->delete();

        return redirect()->route('s3.index');
    }
}