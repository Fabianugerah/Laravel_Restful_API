<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KategoriBeritaController extends Controller
{
        /**
    *    @OA\Get(
    *       path="/kategori-berita",
    *       tags={"Example"},
    *       summary="Kategori Berita",
    *       description="Mengambil Data Kategori Berita",
    *       @OA\Response(
    *           response="200",
    *           description="OK",
    *           @OA\JsonContent
    *           (example={
    *               "success": true,
    *               "message": "Berhasil mengambil Kategori Berita",
    *               "data": {
    *                   {
    *                   "id": "1",
    *                   "username": "Nugrah",
    *                   "password": "rahasia",

    *                  }
    *              }
    *          }),
    *      ),
    *  )
    */
    public function listKategoriBerita() {
        return 'true';
    }

}
