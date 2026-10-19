<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cetak Barcode @if($room)Kamar {{ $room->nomor }}@endif</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box}
body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;margin:0;background:#f1f5f9;color:#0f172a}
.screen-wrap{max-width:1100px;margin:0 auto;padding:20px}
.topbar{background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;border-radius:16px;padding:22px 24px;display:flex;flex-wrap:wrap;gap:16px;align-items:center;justify-content:space-between;box-shadow:0 10px 30px -12px rgba(15,23,42,.5);position:relative;overflow:hidden}
.topbar::after{content:"";position:absolute;right:-40px;top:-40px;width:180px;height:180px;background:radial-gradient(circle,rgba(245,158,11,.25),transparent 70%);border-radius:50%}
.topbar .brand{display:flex;align-items:center;gap:12px}
.topbar .brand-ic{width:46px;height:46px;border-radius:12px;background:rgba(245,158,11,.15);color:#fbbf24;display:flex;align-items:center;justify-content:center;font-size:20px;border:1px solid rgba(245,158,11,.3)}
.topbar h1{margin:0;font-size:20px;font-weight:800;letter-spacing:-.01em}
.topbar p{margin:3px 0 0;font-size:12.5px;color:#94a3b8}
.toolbar{margin-top:16px;display:flex;flex-wrap:wrap;gap:10px;align-items:center;background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:14px 16px;box-shadow:0 4px 14px -8px rgba(15,23,42,.25)}
.btn{display:inline-flex;align-items:center;gap:8px;border:none;cursor:pointer;font-family:inherit;font-size:13.5px;font-weight:700;padding:11px 18px;border-radius:10px;transition:.2s;text-decoration:none}
.btn-print{background:#f59e0b;color:#0f172a;box-shadow:0 6px 16px -6px rgba(245,158,11,.7)}
.btn-print:hover{background:#d97706;transform:translateY(-1px)}
.btn-ghost{background:#f8fafc;color:#475569;border:1px solid #e2e8f0}
.btn-ghost:hover{background:#f1f5f9}
.cols{display:flex;align-items:center;gap:8px;margin-left:auto;font-size:12.5px;color:#64748b;font-weight:600}
.cols button{border:1px solid #e2e8f0;background:#fff;color:#475569;border-radius:8px;padding:8px 13px;font-weight:700;cursor:pointer;font-size:13px;transition:.15s}
.cols button:hover{border-color:#f59e0b}
.cols button.active{background:#0f172a;color:#fff;border-color:#0f172a}
.preview-hint{margin:14px 2px 0;font-size:12px;color:#94a3b8;display:flex;align-items:center;gap:6px}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:12px}
.grid.cols-2{grid-template-columns:repeat(2,1fr)}
.grid.cols-3{grid-template-columns:repeat(3,1fr)}
.grid.cols-4{grid-template-columns:repeat(4,1fr)}
.label{border:1.5px dashed #94a3b8;border-radius:10px;padding:10px 8px;text-align:center;background:#fff;break-inside:avoid}
.label .brand{font-size:9px;font-weight:800;letter-spacing:.12em;color:#0f172a;text-transform:uppercase}
.label .nama{font-size:11.5px;font-weight:600;color:#334155;margin:3px 0 5px;min-height:14px;line-height:1.15}
.label img{max-width:100%;height:auto;display:block;margin:0 auto}
.label .kode{font-family:monospace;font-size:11px;font-weight:700;letter-spacing:.05em;color:#0f172a;margin-top:3px}
.label .kamar{font-size:8.5px;color:#94a3b8;margin-top:2px;letter-spacing:.04em;text-transform:uppercase}
.print-head{display:none}
@page{margin:12mm}
@media print{
 .no-print{display:none !important}
 body{background:#fff}
 .screen-wrap{max-width:none;padding:0}
 .grid{margin-top:0}
 .label{border:1px dashed #888;border-radius:0}
 .print-head{display:block;text-align:center;margin-bottom:10px}
 .print-head h1{font-size:15px;margin:0;letter-spacing:.02em}
 .print-head p{font-size:11px;color:#444;margin:2px 0 0}
}
*{-webkit-print-color-adjust:exact;print-color-adjust:exact}
@media (max-width:640px){ .grid{grid-template-columns:repeat(2,1fr)} }
</style>
</head>
<body>
<div class="screen-wrap">

  {{-- ===== Topbar (layar) ===== --}}
  <div class="topbar no-print">
    <div class="brand">
      <div class="brand-ic"><i class="fa fa-barcode"></i></div>
      <div>
        <h1>@if($room)Cetak Barcode — Kamar {{ $room->nomor }}@else Cetak Barcode Inventaris @endif</h1>
        <p>@if($room){{ optional($room->building)->nama ?? '' }}{{ (optional($room->building)->nama && optional($room->roomType)->nama) ? ' • ' : '' }}{{ optional($room->roomType)->nama ?? '' }} &bull; {{ count($units) }} label@else {{ count($units) }} label siap cetak @endif</p>
      </div>
    </div>
  </div>

  {{-- ===== Toolbar (layar) ===== --}}
  <div class="toolbar no-print">
    <button class="btn btn-print" onclick="window.print()"><i class="fa fa-print"></i>Print / Simpan PDF</button>
    @if($room)
    <a href="{{ route('admin.rooms.show',$room) }}" class="btn btn-ghost"><i class="fa fa-arrow-left"></i>Detail Kamar {{ $room->nomor }}</a>
    @endif
    <div class="cols">
      <span><i class="fa fa-table-cells"></i> Kolom</span>
      <button type="button" onclick="setCols(2,this)">2</button>
      <button type="button" class="active" onclick="setCols(3,this)">3</button>
      <button type="button" onclick="setCols(4,this)">4</button>
    </div>
  </div>
  <div class="preview-hint no-print"><i class="fa fa-circle-info"></i>Preview di layar. Label dirancang hemat tinta (hitam-putih) agar hasil cetak rapi.</div>

  {{-- ===== Header cetak (hanya tercetak) ===== --}}
  @if($room)
  <div class="print-head"><h1>BARCODE KAMAR {{ strtoupper($room->nomor) }}</h1><p>{{ optional($room->building)->nama ?? '' }}{{ (optional($room->building)->nama && optional($room->roomType)->nama) ? ' • ' : '' }}{{ optional($room->roomType)->nama ?? '' }} — {{ count($units) }} label</p></div>
  @else
  <div class="print-head"><h1>CENDANA RESIDENT — BARCODE INVENTARIS</h1></div>
  @endif

  {{-- ===== Grid label ===== --}}
  <div id="gridLabel" class="grid cols-3">
@foreach($units as $u)
<div class="label">
  <div class="brand">Cendana Resident</div>
  <div class="nama">{{ optional($u->item)->nama }}</div>
  <img src="data:image/png;base64,{!! \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($u->kode_barcode,'C128',2,45) !!}" alt="{{ $u->kode_barcode }}">
  <div class="kode">{{ $u->kode_barcode }}</div>
  @if($room)<div class="kamar">Kamar {{ $room->nomor }}</div>@endif
</div>
@endforeach
</div>

</div>
<script>
function setCols(n, btn){
  var g=document.getElementById('gridLabel');
  g.className='grid cols-'+n;
  document.querySelectorAll('.cols button').forEach(function(b){b.classList.remove('active')});
  if(btn)btn.classList.add('active');
}
</script>
</body>
</html>
