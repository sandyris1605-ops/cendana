<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $r)
    {
        $q = Room::with(['building','roomType','activeTenancy.tenant']);
        if ($r->status) $q->where('status', $r->status);
        if ($r->search) $q->where('nomor','like','%'.$r->search.'%');
        $rooms = $q->orderBy('nomor')->paginate(20)->withQueryString();
        $stat = [
            'total' => Room::count(),
            'tersedia' => Room::where('status','tersedia')->count(),
            'terisi' => Room::where('status','terisi')->count(),
            'maintenance' => Room::where('status','maintenance')->count(),
        ];
        return view('admin.rooms.index', compact('rooms','stat') + ['types'=>RoomType::orderBy('nama')->get(),'buildings'=>Building::orderBy('nama')->get()]);
    }
    public function create()
    {
        return view('admin.rooms.form', ['room'=>new Room(),'types'=>RoomType::all(),'buildings'=>Building::all()]);
    }
    public function store(Request $request)
    {
        $request->validate(['nomor'=>'required|unique:rooms,nomor','room_type_id'=>'required|exists:room_types,id','status'=>'nullable|in:tersedia,terisi,maintenance']);
        Room::create($request->only('nomor','room_type_id','building_id','lantai','status','catatan'));
        return redirect()->route('admin.rooms.index')->with('ok','Kamar ditambah.');
    }
    public function show(Room $room)
    {
        $room->load(['roomType','building','assets.unit.item','tenancies.tenant']);
        $roomsTersedia = Room::with('roomType')
            ->where('status', 'tersedia')
            ->where('id', '!=', $room->id)
            ->orderBy('nomor')
            ->get();
        return view('admin.rooms.show', compact('room', 'roomsTersedia'));
    }
    public function edit(Room $room)
    {
        return view('admin.rooms.form', ['room'=>$room,'types'=>RoomType::all(),'buildings'=>Building::all()]);
    }
    public function update(Request $request, Room $room)
    {
        $request->validate(['nomor'=>'required|unique:rooms,nomor,'.$room->id,'room_type_id'=>'required|exists:room_types,id','status'=>'nullable|in:tersedia,terisi,maintenance']);
        $room->update($request->only('nomor','room_type_id','building_id','lantai','status','catatan'));
        return redirect()->route('admin.rooms.index')->with('ok','Kamar diupdate.');
    }
    public function destroy(Room $room)
    {
        if ($room->status !== 'tersedia') {
            return back()->withErrors(['kunci' => 'Kamar '.$room->nomor.' berstatus "'.$room->status.'" — hanya kamar tersedia yang bisa dihapus.']);
        }
        if ($room->activeTenancy()->exists()) {
            return back()->withErrors(['kunci' => 'Kamar '.$room->nomor.' masih dihuni — klik "Penghuni Keluar" dulu sebelum menghapus.']);
        }
        $jmlAset = $room->activeAssets()->count();
        if ($jmlAset > 0) {
            return back()->withErrors(['kunci' => 'Kamar '.$room->nomor.' masih memuat '.$jmlAset.' unit barang — lepas dulu barangnya sebelum menghapus.']);
        }
        \DB::transaction(function () use ($room) {
            $room->tenancies()->delete();
            $room->assets()->delete();
            $room->delete();
        });
        return redirect()->route('admin.rooms.index')->with('ok','Kamar '.$room->nomor.' dihapus. Riwayat hunian ikut terhapus.');
    }
}
