<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    /**
     * GET /medias/{id} — image publique (logo, photos des agences),
     * mise en cache par les navigateurs et l'application.
     */
    public function afficher(Request $request, int $id)
    {
        $media = Media::withoutGlobalScope('sans_contenu')->findOrFail($id);
        $etag = '"m' . $media->id . '-' . ($media->updated_at?->timestamp ?? 0) . '"';

        if ($request->header('If-None-Match') === $etag) {
            return response('', 304)->header('ETag', $etag);
        }

        return response($media->contenu, 200, [
            'Content-Type'   => $media->mime,
            'Content-Length' => strlen($media->contenu),
            'Cache-Control'  => 'public, max-age=31536000, immutable',
            'ETag'           => $etag,
        ]);
    }
}
