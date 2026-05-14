<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Patricia & Mihai - Pozele voastre</title>
    @vite(['resources/css/app.css', 'resources/js/poze-upload.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap');

        @font-face {
            font-family: 'Wedding';
            src: url('{{ asset('fonts/Wedding.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'Ovo';
            src: url('{{ asset('fonts/Ovo-Regular.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        .font-wedding { font-family: 'Wedding', sans-serif; }
        .font-ovo { font-family: 'Ovo', sans-serif; }
        .font-body { font-family: "Open Sans", sans-serif; }
        .bg-cream { background-color: #f5f1e8; }
        .text-burgundy { color: #7d2e3d; }
        .border-burgundy { border-color: #7d2e3d; }
        .bg-burgundy { background-color: #7d2e3d; }
        .text-cream { color: #f5f1e8; }

        body {
            background: linear-gradient(135deg, #f5f1e8 0%, #ede8dc 100%);
        }

        .file-row { animation: fadeSlideIn 0.3s ease forwards; }
        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .progress-track {
            height: 6px;
            background: rgba(125, 46, 61, 0.12);
            border-radius: 999px;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            background: #7d2e3d;
            transition: width 0.2s ease;
        }
        .progress-bar.failed { background: #b91c1c; }
    </style>
</head>
<body class="font-body bg-cream min-h-screen flex items-center justify-center p-4"
      data-upload-url="{{ route('poze.upload.store') }}"
      data-max-files="20"
      data-max-size-bytes="{{ 3 * 1024 * 1024 }}"
      data-concurrency="4"
      data-max-retries="2">

<div class="max-w-md w-full bg-cream rounded-t-full shadow-2xl p-8 md:p-12 relative overflow-hidden"
     style="background-image: url({{ asset('images/background_2.jpg') }})">

    <div class="absolute inset-4 border-2 border-burgundy rounded-t-full opacity-10 pointer-events-none"></div>

    <div class="text-center mt-25 mb-5">
        <h1 class="text-burgundy text-4xl md:text-5xl mb-4 font-wedding">Patricia & Mihai</h1>
        <p class="text-burgundy text-base font-light">Împărtășiți cu noi pozele voastre</p>
    </div>

    <div class="text-center mb-5">
        <p class="text-burgundy text-2xl font-ovo">Albumul nuntii</p>
    </div>

    <div class="text-center mb-6">
        <p class="text-burgundy text-sm md:text-base font-light leading-relaxed">
            Alegeti pana la <span class="font-semibold">20 de poze</span> din galerie<br>
        </p>
    </div>

    <div class="text-center border-t-2 border-burgundy pt-6">

        <label for="poze-input"
               class="block w-full cursor-pointer bg-burgundy text-cream px-8 py-3 rounded-lg font-semibold text-sm hover:opacity-90 transition-opacity shadow-md">
            Alege poze din galerie
        </label>
        <input id="poze-input"
               type="file"
               accept="image/jpeg,image/png,image/webp,image/heic,image/heif"
               multiple
               class="hidden">

        <p id="poze-message" class="error-msg hidden mt-3 text-xs text-red-700"></p>

        <div id="poze-summary" class="mt-5 text-burgundy text-sm hidden">
            <span class="font-ovo text-lg">
                <span id="poze-done">0</span> / <span id="poze-total">0</span>
            </span>
            <span class="block text-xs mt-1 opacity-80">poze incarcate</span>
            <span id="poze-failed-line" class="hidden block text-xs mt-1 text-red-700">
                <span id="poze-failed">0</span> au esuat
            </span>
        </div>

        <ul id="poze-list" class="mt-5 space-y-2 text-left"></ul>

        <button id="poze-retry-all"
                type="button"
                class="hidden mt-5 w-full border-2 border-burgundy text-burgundy px-8 py-3 rounded-lg font-semibold text-sm hover:bg-burgundy hover:text-cream transition-colors shadow-md">
            Reincearca pozele esuate
        </button>
    </div>

    <div class="text-center mt-8">
        <p class="font-wedding text-burgundy text-2xl">Multumim!</p>
    </div>
</div>

<template id="poze-item-template">
    <li class="file-row flex items-center gap-3 bg-cream/70 backdrop-blur-sm border border-burgundy/20 rounded-lg p-3">
        <div class="flex-1 min-w-0">
            <div class="flex justify-between items-baseline gap-2">
                <span class="truncate text-xs text-burgundy font-semibold" data-role="name"></span>
                <span class="text-[10px] text-burgundy opacity-70 font-ovo" data-role="status"></span>
            </div>
            <div class="progress-track mt-2">
                <div class="progress-bar" style="width:0%" data-role="bar"></div>
            </div>
            <p class="hidden mt-1 text-[10px] text-red-700" data-role="error"></p>
        </div>
        <button type="button"
                class="hidden text-[10px] border-2 border-burgundy text-burgundy px-2 py-1 rounded-md font-semibold hover:bg-burgundy hover:text-cream transition-colors"
                data-role="retry">
            Reincearca
        </button>
    </li>
</template>

</body>
</html>
