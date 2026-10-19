<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Setting;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function home() {
        $types = RoomType::withCount(['rooms','rooms as tersedia' => function($q){
            $q->where('status','tersedia');
        }])->get();
        $rooms = Room::with(['roomType','activeTenancy.tenant'])->orderBy('nomor')->get();
        return view('front.home', [
            'types' => $types, 'rooms' => $rooms,
            'nama' => Setting::get('nama','Cendana Resident'),
            'alamat' => Setting::get('alamat'),
            'wa' => Setting::get('wa','6281234567890'),
        ]);
    }
    public function kamar() {
        $rooms = Room::with('roomType')->orderBy('nomor')->get();
        $types = RoomType::all();
        return view('front.kamar', compact('rooms','types'));
    }
    public function booking(Request $r) {
        // Anti-spam: honeypot — form asli HARUS menyertakan input tersembunyi
        // bernama "website" yang dibiarkan KOSONG (manusia tidak mengisinya).
        // Bot mengisinya / tidak mengirimkannya sama sekali → buang diam-diam
        // dengan sukses palsu (agar bot tidak sadar sudah diblokir).
        if ($r->filled('website') || !$r->has('website')) {
            return back()->with('ok','Terima kasih! Tim kami akan menghubungi via WhatsApp.');
        }
        $r->validate([
            'nama'    => 'required|string|max:100',
            'telepon' => 'required|string|max:20|regex:/^[0-9+\-\s()]{6,20}$/',
            'pesan'   => 'nullable|string|max:500',
            'room_type_id' => 'nullable|exists:room_types,id',
            'tanggal_rencana' => 'nullable|date',
        ]);
        Booking::create($r->only('nama','telepon','room_type_id','tanggal_rencana','pesan'));
        return back()->with('ok','Terima kasih! Tim kami akan menghubungi via WhatsApp.');
    }
}
