<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HlsController extends Controller
{
    public function index(Request $request, string $path)
    {
        // Basic path traversal protection
        $path = ltrim($path, '/');
        if (str_contains($path, '..') || str_contains($path, "\\")) {
            abort(404);
        }

        $baseDir = public_path('videos/hls');
        $fullPath = $baseDir . DIRECTORY_SEPARATOR . $path;

        $realBase = realpath($baseDir);
        $realFile = realpath($fullPath);
        if ($realBase === false || $realFile === false || !str_starts_with($realFile, $realBase)) {
            abort(404);
        }

        if (!is_file($realFile) || !is_readable($realFile)) {
            abort(404);
        }

        $extension = pathinfo($realFile, PATHINFO_EXTENSION);

        $mime = $extension === 'm3u8'
            ? 'application/vnd.apple.mpegurl'
            : 'video/mp2t';

        $size = filesize($realFile);
        $lastModified = filemtime($realFile) ?: time();

        $headers = [
            'Content-Type' => $mime,
            'Accept-Ranges' => 'bytes',
            // HLS works best with caching of segments; tweak if you have frequent re-encodes.
            'Cache-Control' => $extension === 'ts' ? 'public, max-age=31536000, immutable' : 'public, max-age=60',
            'Last-Modified' => gmdate('D, d M Y H:i:s', $lastModified) . ' GMT',
            // CORS headers (override/ensure even if your global CORS config doesn't apply to this route)
            'Access-Control-Allow-Origin' => $request->headers->get('Origin') ?? '*',
            'Vary' => 'Origin',
            'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers' => $request->header('Access-Control-Request-Headers', '*'),
        ];

        // Preflight
        if ($request->isMethod('OPTIONS')) {
            return response('', 204, $headers);
        }

        // Conditional GET
        $ifModifiedSince = $request->headers->get('If-Modified-Since');
        if ($ifModifiedSince && strtotime($ifModifiedSince) >= $lastModified) {
            return response('', 304, $headers);
        }

        $range = $request->header('Range');
        if ($range && preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
            $start = (int) $matches[1];
            $end = $matches[2] === '' ? ($size - 1) : (int) $matches[2];

            if ($start >= $size || $end < $start) {
                return response('', 416, array_merge($headers, [
                    'Content-Range' => 'bytes */' . $size,
                ]));
            }

            $end = min($end, $size - 1);
            $length = $end - $start + 1;

            $headers['Content-Length'] = (string) $length;
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";

            $handle = fopen($realFile, 'rb');
            if ($handle === false) {
                abort(404);
            }
            fseek($handle, $start);

            return response()->stream(function () use ($handle, $length) {
                $remaining = $length;
                while ($remaining > 0 && !feof($handle)) {
                    $chunk = fread($handle, min(8192, $remaining));
                    if ($chunk === false) {
                        break;
                    }
                    echo $chunk;
                    $remaining -= strlen($chunk);
                    if (function_exists('flush')) {
                        flush();
                    }
                }
                fclose($handle);
            }, 206, $headers);
        }

        $headers['Content-Length'] = (string) $size;

        return response()->stream(function () use ($realFile) {
            $handle = fopen($realFile, 'rb');
            if ($handle === false) {
                return;
            }
            while (!feof($handle)) {
                $chunk = fread($handle, 8192);
                if ($chunk === false) {
                    break;
                }
                echo $chunk;
                if (function_exists('flush')) {
                    flush();
                }
            }
            fclose($handle);
        }, 200, $headers);
    }
}
