@extends('LoginScreen.layout')
@section('content')

<div class="w-full rounded-lg bg-slate-900 p-10 text-sm text-indigo-300 sm:w-96 max-w-96 mt-18">
    <h1 class="mb-4 text-center text-3xl font-semibold text-white">Create Account</h1>
    <p class="mb-6 text-center text-md">Create your account now!</p>
    
    <form method="POST" action="{{ route('register-user') }}">
        @csrf
        <div class="mb-5 flex gap-3 rounded-full bg-[#333A5c] px-6 py-3">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4">
                <path
                    d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                    stroke="#64748b" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <input type="text" placeholder="Full Name" value="{{ old('name') }}" name="name" class="border-none outline-none"  />
        </div>
        @php if ($errors->has('name')): @endphp
                <span class="text-red-500 text-sm">{{ $errors->first('name') }}</span>
            @php endif; @endphp
        <div class="mb-5 flex gap-3 rounded-full bg-[#333A5c] px-6 py-3">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4">
                <path
                    d="M3 8L8.44992 11.6333C9.73295 12.4886 10.3745 12.9163 11.0678 13.0825C11.6806 13.2293 12.3194 13.2293 12.9322 13.0825C13.6255 12.9163 14.2671 12.4886 15.5501 11.6333L21 8M6.2 19H17.8C18.9201 19 19.4802 19 19.908 18.782C20.2843 18.5903 20.5903 18.2843 20.782 17.908C21 17.4802 21 16.9201 21 15.8V8.2C21 7.0799 21 6.51984 20.782 6.09202C20.5903 5.71569 20.2843 5.40973 19.908 5.21799C19.4802 5 18.9201 5 17.8 5H6.2C5.0799 5 4.51984 5 4.09202 5.21799C3.71569 5.40973 3.40973 5.71569 3.21799 6.09202C3 6.51984 3 7.07989 3 8.2V15.8C3 16.9201 3 17.4802 3.21799 17.908C3.40973 18.2843 3.71569 18.5903 4.09202 18.782C4.51984 19 5.07989 19 6.2 19Z"
                    stroke="#64748b" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <input type="email" placeholder="Email" value="{{ old('email') }}" name="email" class="border-none outline-none"  />
            @php if ($errors->has('email')): @endphp
                <span class="text-red-500 text-sm">{{ $errors->first('email') }}</span>
            @php endif; @endphp
        </div>
        <div class="mb-5 flex gap-3 rounded-full bg-[#333A5c] px-6 py-3">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4">
                <path
                    d="M12 14.5V16.5M7 10.0288C7.47142 10 8.05259 10 8.8 10H15.2C15.9474 10 16.5286 10 17 10.0288M7 10.0288C6.41168 10.0647 5.99429 10.1455 5.63803 10.327C5.07354 10.6146 4.6146 11.0735 4.32698 11.638C4 12.2798 4 13.1198 4 14.8V16.2C4 17.8802 4 18.7202 4.32698 19.362C4.6146 19.9265 5.07354 20.3854 5.63803 20.673C6.27976 21 7.11984 21 8.8 21H15.2C16.8802 

                    stroke="#64748b" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <input type="password" placeholder="Password" name="password" class="border-none outline-none"  />
            @php if ($errors->has('password')): @endphp
                <span class="text-red-500 text-sm">{{ $errors->first('password') }}</span>
            @php endif; @endphp
        </div>
        <button type="submit"
            class="w-full rounded-full bg-gradient-to-r from-indigo-400 to-indigo-900 py-3 font-medium tracking-wide text-white cursor-pointer">Sign
            Up</button>
    </form>
    <p class="mt-4 mb-7 text-center text-sm text-slate-400">Already Have an account?
        <a href="{{ route('login') }}" class="hover:underline text-blue-400">Login</a>
    </p>
    <span class="block w-full h-0.5 bg-gradient-to-r from-slate-900 via-indigo-500 to-slate-900"></span>
    <div class="mt-6 flex justify-center w-full gap-6">
        <button
            class="bg-[#333A5c] px-4 py-1.5 w-full gap-0.5 max-w-56 rounded-lg flex justify-center items-center cursor-pointer">
            <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"
                class="w-7 h-7 rounded-full border-slate-500">
                <path
                    d="M30.0014 16.3109C30.0014 15.1598 29.9061 14.3198 29.6998 13.4487H16.2871V18.6442H24.1601C24.0014 19.9354 23.1442 21.8798 21.2394 23.1864L21.2127 23.3604L25.4536 26.58L25.7474 26.6087C28.4458 24.1665 30.0014 20.5731 30.0014 16.3109Z"
                    fill="#4285F4"></path>
                <path
                    d="M16.2863 29.9998C20.1434 29.9998 23.3814 28.7553 25.7466 26.6086L21.2386 23.1863C20.0323 24.0108 18.4132 24.5863 16.2863 24.5863C12.5086 24.5863 9.30225 22.1441 8.15929 18.7686L7.99176 18.7825L3.58208 22.127L3.52441 22.2841C5.87359 26.8574 10.699 29.9998 16.2863 29.9998Z"
                    fill="#34A853"></path>
                <path
                    d="M8.15964 18.769C7.85806 17.8979 7.68352 16.9645 7.68352 16.0001C7.68352 15.0356 7.85806 14.1023 8.14377 13.2312L8.13578 13.0456L3.67083 9.64746L3.52475 9.71556C2.55654 11.6134 2.00098 13.7445 2.00098 16.0001C2.00098 18.2556 2.55654 20.3867 3.52475 22.2845L8.15964 18.769Z"
                    fill="#FBBC05"></path>
                <path
                    d="M16.2864 7.4133C18.9689 7.4133 20.7784 8.54885 21.8102 9.4978L25.8419 5.64C23.3658 3.38445 20.1435 2 16.2864 2C10.699 2 5.8736 5.1422 3.52441 9.71549L8.14345 13.2311C9.30229 9.85555 12.5086 7.4133 16.2864 7.4133Z"
                    fill="#EB4335"></path>
                </g>
            </svg>
            <span class="text-indigo-100 tracking-wide text-base w-full">Google<span>
        </button>
        <button
            class="bg-[#333A5c] px-4 py-1.5 w-full gap-0.5 max-w-56 rounded-lg flex justify-center items-center cursor-pointer">
            <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-7 h-7  rounded-full">
                <circle cx="16" cy="16" r="14" fill="url(#paint0_linear_87_7208)"></circle>
                <path
                    d="M21.2137 20.2816L21.8356 16.3301H17.9452V13.767C17.9452 12.6857 18.4877 11.6311 20.2302 11.6311H22V8.26699C22 8.26699 20.3945 8 18.8603 8C15.6548 8 13.5617 9.89294 13.5617 13.3184V16.3301H10V20.2816H13.5617V29.8345C14.2767 29.944 15.0082 30 15.7534 30C16.4986 30 17.2302 29.944 17.9452 29.8345V20.2816H21.2137Z"
                    fill="white"></path>
                <defs>
                    <linearGradient id="paint0_linear_87_7208" x1="16" y1="2" x2="16" y2="29.917"
                        gradientUnits="usPlerSpaceOnUse">
                        <stop stop-color="#18ACFE"></stop>
                        <stop offset="1" stop-color="#0163E0"></stop>
                    </linearGradient>
                </defs>
                </g>
            </svg>
            <span class="text-indigo-100 tracking-wide text-base w-full">Facebook<span>
        </button>
    </div>
</div>
@endsection