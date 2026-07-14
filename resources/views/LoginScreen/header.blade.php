<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script> -->
    <title>Admin Panel</title>
</head>

<body>

    <div
        class="flex min-h-screen items-center justify-center bg-gradient-to-br from-blue-200 to-purple-400 relative pb-32">
        <div class="absolute top-4 left-6 flex justify-center items-center gap-2">
            <svg height="200px" width="200px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 492.481 492.481" xml:space="preserve"
                fill="#000000" class='w-6 h-6'>
                <linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="-36.6002" y1="621.3422" x2="-17.2782"
                    y2="547.7642" gradientTransform="matrix(7.8769 0 0 -7.8769 404.0846 4917.9966)">
                    <stop offset="0" style="stop-color:#29D3DA"></stop>
                    <stop offset="0.519" style="stop-color:#0077FF"></stop>
                    <stop offset="0.999" style="stop-color:#064093"></stop>
                    <stop offset="1" style="stop-color:#084698"></stop>
                </linearGradient>
                <polygon style="fill:url(#SVGID_1_);" points="25.687,297.141 135.735,0 271.455,0 161.398,297.141 ">
                </polygon>
                <linearGradient id="SVGID_2_" gradientUnits="userSpaceOnUse" x1="-27.0735" y1="620.7541" x2="-11.7045"
                    y2="560.3241" gradientTransform="matrix(7.8769 0 0 -7.8769 404.0846 4917.9966)">
                    <stop offset="0.012" style="stop-color:#E0B386"></stop>
                    <stop offset="0.519" style="stop-color:#DA498C"></stop>
                    <stop offset="1" style="stop-color:#961484"></stop>
                </linearGradient>
                <polygon style="fill:url(#SVGID_2_);"
                    points="123.337,394.807 233.409,97.674 369.144,97.674 259.072,394.807 "></polygon>
                <linearGradient id="SVGID_3_" gradientUnits="userSpaceOnUse" x1="14.0324" y1="554.688" x2="-10.4176"
                    y2="584.028" gradientTransform="matrix(7.8769 0 0 -7.8769 404.0846 4917.9966)">
                    <stop offset="0" style="stop-color:#29D3DA"></stop>
                    <stop offset="0.519" style="stop-color:#0077FF"></stop>
                    <stop offset="0.999" style="stop-color:#064093"></stop>
                    <stop offset="1" style="stop-color:#084698"></stop>
                </linearGradient>
                <polygon style="fill:url(#SVGID_3_);"
                    points="221.026,492.481 331.083,195.348 466.794,195.348 356.746,492.481 "></polygon>
            </svg>
            <p class="text-2xl text-indigo-500 tracking-wide font-bold">Logo</p>
            <x-login-alert />
        </div>
        