import Alpine from "alpinejs";

// Leaflet (peta). Import gambar dan mergeOptions memperbaiki ikon pin
// yang rusak saat Leaflet dibundel Vite.
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import markerIcon from "leaflet/dist/images/marker-icon.png";
import markerIcon2x from "leaflet/dist/images/marker-icon-2x.png";
import markerShadow from "leaflet/dist/images/marker-shadow.png";

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});
window.L = L;

window.Alpine = Alpine;
Alpine.start();

// Menggambar QR code (SVG) di dalam elemen. Pustaka qrcode baru dimuat
// saat dibutuhkan, jadi halaman pemesanan pelanggan tetap ringan.
window.renderQr = async (el, text) => {
    const QRCode = (await import("qrcode")).default;
    el.innerHTML = await QRCode.toString(text, { type: "svg", margin: 1 });
};
