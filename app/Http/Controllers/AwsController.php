<?php

namespace App\Http\Controllers;

use Aws\Rekognition\RekognitionClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AwsController extends Controller
{

    protected $client;

    public function __construct(RekognitionClient $client)
    {
        $this->client = $client;
    }




    public function getAllMatchesImagees(Request $request)
    {
        $nameExternalId = collect();
        $image = $request->file('image');
        $nameCollection = $request->input('nameCollection');
        $result = $this->client->searchFacesByImage([
            'CollectionId' => $nameCollection,
            'FaceMatchThreshold' => 70,
            'Image' => [
                'Bytes' => file_get_contents($image),
            ],
            'MaxFaces' => 10,
        ]);

        foreach ($result['FaceMatches'] as $key => $value) {
            $nameExternalId->push($value['Face']['ExternalImageId']);
        }

        return response()->json(['message' => $nameExternalId]);
    }

    public function saveMultipleImages(Request $request)
    {

        $images = $request->file('images');
        $folder = $request->input('folder');

        try {
            foreach ($images as $image) {
                $fileName = $image->getClientOriginalName();
                Storage::disk('s3')->put($folder . '/' . $fileName, file_get_contents($image));
                $this->client->indexFaces(
                    [
                        'CollectionId' => $folder,
                        'DetectionAttributes' => ['ALL'],
                        'ExternalImageId' => $fileName,
                        'Image' => [
                            'S3Object' => [
                                'Bucket' => env('AWS_BUCKET'),
                                'Name' => $folder . '/' . $fileName,
                            ],
                        ],
                    ]
                );
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()]);
        }
        return response()->json(['message' => 'images saved successfully', 'nombre' => $fileName]);
    }
}
