<?php

namespace App\Http\Controllers;

use App\Models\Camera;
use Aws\KinesisVideo\KinesisVideoClient;
use Aws\KinesisVideoArchivedMedia\KinesisVideoArchivedMediaClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class LiveStreamController extends Controller
{
    public function adminShow(Camera $camera): View
    {
        return view('live.show', ['camera' => $camera, 'sessionEndpoint' => route('cameras.live.session', $camera)]);
    }

    public function adminSession(Camera $camera): JsonResponse
    {
        return $this->sessionResponse($camera);
    }

    public function portalSession(Request $request, Camera $camera): JsonResponse
    {
        abort_unless($request->user()->cameras()->whereKey($camera->id)->exists(), 404);
        return $this->sessionResponse($camera);
    }

    private function sessionResponse(Camera $camera): JsonResponse
    {
        if (filled($camera->aws_stream_name)) {
            try {
                $region = config('vigila.aws_region');
                $videoClient = new KinesisVideoClient(['version' => 'latest', 'region' => $region]);
                $endpoint = $videoClient->getDataEndpoint([
                    'APIName' => 'GET_HLS_STREAMING_SESSION_URL',
                    'StreamName' => $camera->aws_stream_name,
                ])->get('DataEndpoint');

                $mediaClient = new KinesisVideoArchivedMediaClient([
                    'version' => 'latest', 'region' => $region, 'endpoint' => $endpoint,
                ]);
                $url = $mediaClient->getHLSStreamingSessionURL([
                    'StreamName' => $camera->aws_stream_name,
                    'PlaybackMode' => 'LIVE',
                    'Expires' => 300,
                ])->get('HLSStreamingSessionURL');

                return response()->json(['type' => 'hls', 'url' => $url, 'expires_in' => 300]);
            } catch (Throwable $exception) {
                report($exception);
                return response()->json(['message' => 'No se pudo conectar con Kinesis Video Streams. Revisa la región, el nombre del stream y los permisos IAM del servidor.'], 503);
            }
        }

        if (filled($camera->stream_url) && in_array(parse_url($camera->stream_url, PHP_URL_SCHEME), ['http', 'https'], true)
            && ! parse_url($camera->stream_url, PHP_URL_USER) && ! parse_url($camera->stream_url, PHP_URL_PASS)) {
            $path = Str::lower((string) parse_url($camera->stream_url, PHP_URL_PATH));
            $type = Str::endsWith($path, ['.m3u8']) ? 'hls' : (Str::endsWith($path, ['.mjpg', '.mjpeg']) ? 'mjpeg' : 'video');
            return response()->json(['type' => $type, 'url' => $camera->stream_url, 'expires_in' => null]);
        }

        return response()->json(['message' => 'Configura un stream de Kinesis Video Streams o una URL HTTP/HLS válida en el inventario de cámaras.'], 422);
    }
}
