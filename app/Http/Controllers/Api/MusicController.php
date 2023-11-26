<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Music;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MusicController extends Controller
{

    public function index()
    {
        try {
            // Get all music
            $music = Music::with("user")->get();
            // Return the music data
            return response()->json($music);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    public function show($id)
    {
        // Validation
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:music,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        try {
            // $music = Music::findOrFail($id);
            $music = Music::with('user')->findOrFail($id);

            // Return the music data
            return response()->json($music);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json(['error' => 'Music not found'], 404);
        }
        // return response()->json(["success" => true, "message" => "Accessible route"]);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string',
                'artist' => 'required|string',
                'file' => 'required|file|mimes:mp3',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }
            // Store the file in the storage directory
            $file = $request->file('file');
            $filePath = $file->store('music', 'public');

            // $music = new Music();
            // $music->title = $request->input('title');
            // $music->author = $request->input('artist');
            // $music->url = asset('storage/' . $filePath);
            $music = new Music([
                'title' => $request->input('title'),
                'author' => $request->input('artist'),
                'url' => asset('storage/' . $filePath),
                'user_id' => auth()->id(),
            ]);
            $music->save();

            // Return the newly created music item
            return response()->json($music, 201);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json(['error' => 'An error occurred', "description" => $e->getMessage()], 500);
        }
    }
    public function destroy($id)
    {
        try {
            // Retrieve music item by ID
            $music = Music::findOrFail($id);
            if (!$music) {
                return response()->json(["success" => false, "message" => "Music not found"]);
            }
            // Delete the associated file from storage
            $filePath = str_replace(asset('storage/'), '', $music->url);
            Storage::disk('public')->delete($filePath);

            // Delete the music item
            $music->delete();

            // Return a success message
            return response()->json(['message' => 'Music item deleted successfully']);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'title' => 'string',
                'artist' => 'string',
                'file' => 'file|mimes:mp3',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $music = Music::findOrFail($id);

            // Update operation
            $music->title = $request->input('title');
            $music->author = $request->input('artist');
            // Run only if the update request has a file
            if ($request->hasFile('file')) {
                // Delete the old associated file from storage
                $oldFilePath = str_replace(asset('storage/'), '', $music->url);
                Storage::disk('public')->delete($oldFilePath);

                // Store the new file in the storage directory
                $file = $request->file('file');
                $newFilePath = $file->store('music', 'public');

                // Update the file URL in the database
                $music->file_url = asset('storage/' . $newFilePath);
            }


            $music->save();

            // Return the updated music item
            return response()->json($music);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    public function stream($id)
    {
        try {
            // Retrieve music item by ID
            $music = Music::findOrFail($id);

            // Get the file path from the file URL
            $filePath = str_replace(asset('storage/'), '', $music->file_url);

            // Set the content type based on the file type
            $contentType = Storage::mimeType($filePath);

            // Stream the file to the response
            return response()->stream(
                function () use ($filePath) {
                    $stream = Storage::disk('public')->readStream($filePath);
                    fpassthru($stream);
                    fclose($stream);
                },
                200,
                [
                    'Content-Type' => $contentType,
                    'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
                ]
            );
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }
}
