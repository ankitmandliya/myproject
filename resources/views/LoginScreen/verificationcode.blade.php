@extends('LoginScreen.layout')
@section('content')
<div class="w-full max-w-96 rounded-lg bg-slate-900 p-10 text-sm text-indigo-300 sm:w-96 mt-18">
    <h1 class="mb-4 text-center text-3xl font-semibold text-white">Verification Code</h1>
    <p class="mb-6 text-center text-sm">Enter the 4-digit code we've sent to your mail</p>
    <form onSubmit=handleEvent(event)>
        <div class="flex mb-6 gap-4 justify-center">
            <input type="text" placeholder="*"
                class="otp-input outline-none w-12 h-12 rounded-sm text-center bg-[#333A5c] font-base text-xl border-1 border-b-blue-300"
                maxlength="1" required />
            <input type="text" placeholder="*"
                class="otp-input outline-none w-12 h-12 rounded-sm text-center bg-[#333A5c] font-base text-xl border-1 border-b-blue-300"
                maxlength="1" required />
            <input type="text" placeholder="*"
                class="otp-input outline-none w-12 h-12 rounded-sm text-center bg-[#333A5c] font-base text-xl border-1 border-b-blue-300"
                maxlength="1" required />
            <input type="text" placeholder="*"
                class="otp-input outline-none w-12 h-12 rounded-sm text-center bg-[#333A5c] font-base text-xl border-1 border-b-blue-300"
                maxlength="1" required />
        </div>

        <button
            class="w-full cursor-pointer rounded-full bg-gradient-to-r from-indigo-400 to-indigo-900 py-3 font-medium tracking-wide text-white">Verify</button>
    </form>
    <p class="mt-5 text-center text-sm">
        <a href="{{ route('login') }}"
            class="flex items-center justify-center gap-2 text-slate-400 hover:underline">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5">
                <path d="M4 12H20M4 12L8 8M4 12L8 16" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round"></path>
            </svg>
            <span>Back to log in</span>
        </a>
    </p>
</div>
@endsection