<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pusat Data Telkom Property</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        telkomDark: '#121212',
                        telkomSidebar: '#1a1a1a',
                        telkomRed: '#e60000',
                        telkomOrange: '#f97316',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar (Kiri) -->
        <aside class="w-64 bg-[#111111] text-white flex flex-col justify-between border-r border-gray-800 shadow-xl z-20">
            <div>
                <!-- Logo Brand -->
                <div class="p-5 border-b border-gray-800 flex items-center space-x-2">
                    <div class="bg-white px-2 py-1.5 rounded flex items-center justify-center font-bold text-black tracking-tighter">
                        <span class="text-red-600">Telkom</span> <span class="ml-1 text-black font-semibold">Property</span>
                    </div>
                </div>

                <!-- Navigasi Menu -->
                <nav class="mt-4 px-3 space-y-1">
                    <!-- Beranda (Aktif) -->
                    <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg bg-gradient-to-r from-amber-700/60 to-amber-900/40 text-amber-200 border-l-4 border-amber-500 font-medium transition">
                        <i class="fa-solid fa-house w-5 text-center"></i>
                        <span>Beranda</span>
                    </a>
                    <!-- Dokumen -->
                    <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                        <i class="fa-solid fa-folder-open w-5 text-center"></i>
                        <span>Dokumen</span>
                    </a>
                    <!-- Unggah Baru -->
                    <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                        <i class="fa-solid fa-cloud-arrow-up w-5 text-center"></i>
                        <span>Unggah Baru</span>
                    </a>
                    <!-- Histori -->
                    <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition">
                        <i class="fa-solid fa-clock-rotate-left w-5 text-center"></i>
                        <span>Histori</span>
                    </a>
                </nav>
            </div>

            <!-- User Profile di Bawah Sidebar -->
            <div class="p-4 border-t border-gray-800">
                <div class="flex items-center justify-between bg-gray-900 px-3 py-2.5 rounded-lg border border-gray-800">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center text-gray-300">
                            <i class="fa-solid fa-user text-sm"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-200">Telpro BIM</span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs text-gray-400"></i>
                </div>
            </div>
        </aside>

        <!-- Konten Utama (Kanan) -->
        <main class="flex-1 flex flex-col overflow-y-auto bg-gray-100">

            <!-- Top bar header minimalis -->
            <header class="bg-white border-b border-gray-200 px-8 py-3.5 shadow-xs flex justify-between items-center">
                <div class="text-sm text-gray-500 font-medium">
                    Beranda <span class="mx-1">/</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-xs text-gray-400 bg-gray-100 px-3 py-1.5 rounded-full font-medium border border-gray-200">
                        <i class="fa-regular fa-calendar-days mr-1.5 text-gray-500"></i> Selasa, 3 November 2026
                    </span>
                </div>
            </header>

            <!-- Bagian Isi Dashboard -->
            <div class="p-8 space-y-6">

                <!-- Judul Utama Halaman -->
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">
                    Beranda – Pusat Data Telkom Property
                </h1>

                <!-- Tiga Kotak Kartu Statistik (Summary Cards) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Card 1 -->
                    <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200 flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 text-xl border border-gray-200">
                            <i class="fa-regular fa-file-lines"></i>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Total Dokumen</p>
                            <h3 class="text-2xl font-bold text-gray-900 mt-0.5">1,490</h3>
                        </div>
                    </div>
                    <!-- Card 2 -->
                    <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200 flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 text-xl border border-gray-200">
                            <i class="fa-regular fa-file-lines"></i>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Total Dokumen</p>
                            <h3 class="text-2xl font-bold text-gray-900 mt-0.5">1,490</h3>
                        </div>
                    </div>
                    <!-- Card 3 -->
                    <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200 flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 text-xl border border-gray-200">
                            <i class="fa-regular fa-file-lines"></i>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Total Dokumen</p>
                            <h3 class="text-2xl font-bold text-gray-900 mt-0.5">1,490</h3>
                        </div>
                    </div>
                </div>

                <!-- Bagian Aktivitas Terbaru & Tabel -->
                <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden mt-6">
                    
                    <!-- Header Tabel -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Aktivitas Terbaru</h2>
                    </div>

                    <!-- Tabel Data -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-gray-700 text-xs uppercase tracking-wider border-b border-gray-300">
                                    <th class="py-3 px-4 font-semibold w-12 text-center">
                                        <i class="fa-solid fa-table-cells text-gray-400"></i>
                                    </th>
                                    <th class="py-3 px-4 font-semibold">Nama Dokumen</th>
                                    <th class="py-3 px-4 font-semibold">Tipe</th>
                                    <th class="py-3 px-4 font-semibold">Data Lokasi</th>
                                    <th class="py-3 px-4 font-semibold">Tanggal</th>
                                    <th class="py-3 px-4 font-semibold">Pemilik</th>
                                    <th class="py-3 px-4 font-semibold text-center w-48">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                                <!-- Baris 1 -->
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3.5 px-4 text-center text-gray-400"><i class="fa-regular fa-square"></i></td>
                                    <td class="py-3.5 px-4 font-medium text-gray-900">Nama Dokumen</td>
                                    <td class="py-3.5 px-4">Tipe</td>
                                    <td class="py-3.5 px-4">Data Lokasi</td>
                                    <td class="py-3.5 px-4">Tanggal</td>
                                    <td class="py-3.5 px-4">Pemilik</td>
                                    <td class="py-3.5 px-4 text-center space-x-2">
                                        <button class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-medium px-3 py-1.5 rounded-md shadow-xs transition">
                                            <i class="fa-solid fa-download mr-1"></i> Unduh
                                        </button>
                                        <button class="bg-amber-400 hover:bg-amber-500 text-gray-900 text-xs font-semibold px-3 py-1.5 rounded-md shadow-xs transition">
                                            <i class="fa-solid fa-pen-to-square mr-1"></i> Perbarui
                                        </button>
                                    </td>
                                </tr>
                                <!-- Baris 2 -->
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3.5 px-4 text-center text-gray-400"><i class="fa-regular fa-square"></i></td>
                                    <td class="py-3.5 px-4 font-medium text-gray-900">Nama Dokumen</td>
                                    <td class="py-3.5 px-4">Tipe</td>
                                    <td class="py-3.5 px-4">Data Lokasi</td>
                                    <td class="py-3.5 px-4">Tanggal</td>
                                    <td class="py-3.5 px-4">Pemilik</td>
                                    <td class="py-3.5 px-4 text-center space-x-2">
                                        <button class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-medium px-3 py-1.5 rounded-md shadow-xs transition">
                                            <i class="fa-solid fa-download mr-1"></i> Unduh
                                        </button>
                                        <button class="bg-amber-400 hover:bg-amber-500 text-gray-900 text-xs font-semibold px-3 py-1.5 rounded-md shadow-xs transition">
                                            <i class="fa-solid fa-pen-to-square mr-1"></i> Perbarui
                                        </button>
                                    </td>
                                </tr>
                                <!-- Baris 3 -->
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3.5 px-4 text-center text-gray-400"><i class="fa-regular fa-square"></i></td>
                                    <td class="py-3.5 px-4 font-medium text-gray-900">Nama Dokumen</td>
                                    <td class="py-3.5 px-4">Tipe</td>
                                    <td class="py-3.5 px-4">Data Lokasi</td>
                                    <td class="py-3.5 px-4">Tanggal</td>
                                    <td class="py-3.5 px-4">Pemilik</td>
                                    <td class="py-3.5 px-4 text-center space-x-2">
                                        <button class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-medium px-3 py-1.5 rounded-md shadow-xs transition">
                                            <i class="fa-solid fa-download mr-1"></i> Unduh
                                        </button>
                                        <button class="bg-amber-400 hover:bg-amber-500 text-gray-900 text-xs font-semibold px-3 py-1.5 rounded-md shadow-xs transition">
                                            <i class="fa-solid fa-pen-to-square mr-1"></i> Perbarui
                                        </button>
                                    </td>
                                </tr>
                                <!-- Baris 4 -->
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3.5 px-4 text-center text-gray-400"><i class="fa-regular fa-square"></i></td>
                                    <td class="py-3.5 px-4 font-medium text-gray-900">Nama Dokumen</td>
                                    <td class="py-3.5 px-4">Tipe</td>
                                    <td class="py-3.5 px-4">Data Lokasi</td>
                                    <td class="py-3.5 px-4">Tanggal</td>
                                    <td class="py-3.5 px-4">Pemilik</td>
                                    <td class="py-3.5 px-4 text-center space-x-2">
                                        <button class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-medium px-3 py-1.5 rounded-md shadow-xs transition">
                                            <i class="fa-solid fa-download mr-1"></i> Unduh
                                        </button>
                                        <button class="bg-amber-400 hover:bg-amber-500 text-gray-900 text-xs font-semibold px-3 py-1.5 rounded-md shadow-xs transition">
                                            <i class="fa-solid fa-pen-to-square mr-1"></i> Perbarui
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination di Pojok Kanan Bawah Tabel -->
                    <div class="px-6 py-3.5 bg-white border-t border-gray-200 flex justify-end items-center space-x-1 text-sm text-gray-600">
                        <button class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 hover:bg-gray-100 transition disabled:opacity-50">
                            <i class="fa-solid fa-chevron-left text-xs"></i>
                        </button>
                        <button class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 hover:bg-gray-100 transition">
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </button>
                        <button class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 hover:bg-gray-100 transition">
                            <i class="fa-solid fa-angles-right text-xs"></i>
                        </button>
                    </div>

                </div>

            </div>
        </main>
    </div>

</body>
</html>