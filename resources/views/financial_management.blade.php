<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>امور مالی</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://cdn.jsdelivr.net/npm/yekan-font@1.0.0/css/yekan-font.min.css" rel="stylesheet">
        <style>
            *{
                transition: all 0.1s;
            }
            label {
                font-family: yekan;
            }
            @media screen and (-webkit-min-device-pixel-ratio: 0) {
                input[type="range"]::-webkit-slider-thumb {
                    width: 20px;
                    -webkit-appearance: none;
                    appearance: none;
                    height: 20px;
                    cursor: ew-resize;
                    background: #13131355;
                    border: #fff 4px solid;
                    box-shadow: 0 0 0 5px #13131355;
                    border-radius: 50%;
                }
                input[type="range"]{
                    background: linear-gradient(to left, #fa0d4caa,#f6f352,#00ff80aa);
                }
            }
            ::-webkit-scrollbar {
                width: 3px;
                height: 3px;
            }
            ::-webkit-scrollbar-track {
                background: #13131344;
                border-radius: 50px;
            }
            ::-webkit-scrollbar-thumb {
                background: #aaaaaa99;
                border-radius: 50px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: #ffffff99;
            }
        </style>
    </head>
    <body class="antialiased bg-cover bg-center bg-fixed bg-[url(wp.jpg)] min-h-screen text-gray-200 font-[yekan] flex flex-col" dir="rtl">
        <div class="grow flex flex-col p-8 gap-6">

            <div class="container mx-auto bg-white/20 rounded-lg border border-white/25 py-3 px-4 flex items-center justify-between gap-4 px-6 font-extrabold text-xl">
                <div class="flex items-center flex-col sm:flex-row gap-1 sm:gap-4">
                    <h1>                  
                        مدیریت مالی حساب
                    </h1>
                    <div class="relative block sm:inline-block h-0.5 sm:h-5 w-full sm:w-0.5 bg-white/30 rotate-0 sm:rotate-[20deg] rounded-full"></div>
                    <p>{{ $doctor->fullname }}</p>
                </div>
                <a href="#home" class="flex items-center gap-1 text-rose-400 hover:text-rose-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.3" stroke="currentColor" class="inline w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                    </svg>
                    خروج
                </a>
            </div>

            <div class="container mx-auto flex flex-col lg:flex-row gap-6">

                <div class="w-full mx-auto lg:w-1/3">
                    <div class="block w-full h-full bg-white/20 rounded-lg border border-white/25 py-3 px-4">
                        <h1 class="font-bold text-2xl text-center border-b border-white/30 pb-4">دارایی ها</h1>
                        <div class="w-full flex flex-col gap-4 py-4 items-stretch">
                            <div class="w-full bg-white/10 border border-white/10 rounded py-3 px-4 flex flex-col items-center hover:border-white/20">
                                <div class="flex justify-start items-center gap-5 w-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor" class="mr-6 sm:mr-8 lg:mr-12 w-24 h-24 drop-shadow-[0_0_10px_#cc22ffaa]">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                    </svg>
                                    <div class="grow text-center">
                                        <h2 class="font-semibold text-lg mb-2">دارایی ریالی</h2>
                                        <p class="font-mono font-semibold text-[#eeaaff]"><span id="rial_amount">...</span> ریال</p>
                                    </div>
                                </div>
                                <form id="withdraw" action="{{ route('financial_management_withdraw')  }}" method="POST" class="border-t border-white/10 pt-3 my-3 px-3 w-full">
                                    @csrf
                                    <div class="flex justify-between items-center">
                                        <h2 class="text-lg">برداشت موجودی</h2>
                                        <p class="font-mono"><span id="withdraw_rial">0</span> ریال</p>
                                    </div>
                                    <input class="w-full rounded-lg appearance-none h-4 mt-3 mb-4" type="range" name="amount" id="" min="0" max="1" step="1000000" value="0">
                                    <div class="font-sans text-lg font-semibold text-white flex flex-wrap gap-3 justify-between items-stretch">
                                        <input type="submit" name="type" value="selected" id="sub_sellected" class="hidden peer" disabled="true">
                                        <label for="sub_sellected" class="bg-blue-500 py-2 px-4 rounded-lg peer-disabled:bg-gray-500 peer-disabled:cursor-not-allowed whitespace-nowrap enabled:hover:opacity-90 active:scale-[0.95] ">برداشت مقدار انتخاب شده</label>
                                        <input type="submit" name="type" id="sub_full" value="full" class="hidden">
                                        <label for="sub_full" class="bg-emerald-500 py-2 px-4 rounded-lg whitespace-nowrap hover:opacity-90 active:scale-[0.95]">برداشت کامل</label>
                                    </div>
                                </form>
                            </div>

                            <div class="w-full bg-white/10 border border-white/10 rounded py-3 px-4 flex flex-col items-center hover:border-white/20">
                                <div class="flex justify-start items-center gap-5 w-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor" class="mr-6 sm:mr-8 lg:mr-12 w-24 h-24 drop-shadow-[0_0_10px_#ffcc22aa]">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                                    </svg>
                                    <div class="grow text-center">
                                        <h2 class="font-semibold text-lg mb-2">دارایی طلا</h2>
                                        <p class="font-mono font-semibold text-[#ffeeaa]"><span id="gold_amount">...</span> گرم</p>
                                    </div>
                                </div>
                            </div>

                            <div class="w-full bg-white/10 border border-white/10 rounded py-3 px-4 flex flex-col items-center hover:border-white/20">
                                <div class="flex justify-start items-center gap-5 w-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-6 sm:mr-8 lg:mr-12 w-24 h-24 drop-shadow-[0_0_10px_#22ffccaa]">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3" />
                                    </svg>
                                    <div class="grow text-center">
                                        <h2 class="font-semibold text-lg mb-2">دارایی تتر <span class="font-sans">[Tether]</span></h2>
                                        <p class="font-mono font-semibold text-[#aaffee]" dir="ltr"><span id="tether_amount">...</span> USDT</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full mx-auto lg:w-1/3 flex flex-col gap-6">
                    <div class="block w-full grow bg-white/20 rounded-lg border border-white/25 py-3 px-4">
                        <h1 class="font-bold text-2xl text-center border-b border-white/30 pb-4">وضعیت لحظه ای بازار</h1>

                        <div class="w-full flex flex-col gap-4 py-4 items-stretch">

                            <div class="w-full bg-white/10 border border-white/10 rounded py-3 px-4 flex flex-col items-center hover:border-white/20">
                                <div class="flex justify-start items-center gap-5 w-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor" class="mr-6 sm:mr-8 lg:mr-12 w-24 h-24 drop-shadow-[0_0_10px_#ffcc22aa]">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                                    </svg>
                                      
                                    <div class="grow text-center">
                                        <h2 class="font-semibold text-lg mb-2">قیمت طلا</h2>
                                        <p class="font-mono font-semibold text-[#ffeeaa]">هر گرم <span id="gold_price">...</span> ریال</p>
                                    </div>
                                </div>
                            </div>

                            <div class="w-full bg-white/10 border border-white/10 rounded py-3 px-4 flex flex-col items-center hover:border-white/20">
                                <div class="flex justify-start items-center gap-5 w-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-6 sm:mr-8 lg:mr-12 w-24 h-24 drop-shadow-[0_0_10px_#22ffccaa]">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3" />
                                    </svg>
                                      
                                      
                                    <div class="grow text-center">
                                        <h2 class="font-semibold text-lg mb-2">قیمت تتر <span class="font-sans">[Tether]</span></h2>
                                        <p class="font-mono font-semibold text-[#aaffee]">نامشخص</p>
                                        <!-- <p class="font-mono font-semibold text-[#aaffee]" dir="ltr"><span id="tether_price">...</span> ریال</p> -->
                                    </div>
                                </div>
                            
                            </div>


                        </div>
                    </div>
                    <div class="block w-full h-fit bg-white/20 rounded-lg border border-white/25 py-3 px-4">
                        <h1 class="font-bold text-2xl text-center border-b border-white/30 pb-4">تبادل طلا</h1>
                        <div class="w-full flex flex-col gap-4 py-4 items-stretch">
                            <form id="exchange" action="{{ route('financial_management_exchange')  }}" method="POST" class="my-3 px-3 w-full flex flex-col gap-3">
                                @csrf
                                <h2 class="text-lg">میزان تبادل</h2>
                                <input class="w-full rounded-lg appearance-none h-4" type="range" name="amount" min="0" max="1" step="1" value="0">
                                <p class="font-mono font-bold my-5 mx-auto text-lg text-white flex flex-col sm:flex-row items-center justify-center gap-3">
                                    <span class="bg-[#ffeeaa44] border border-[#ffeeaa] py-2 px-4 rounded-lg"><span id="exchange_gold">0</span> گرم</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="rotate-90 sm:rotate-0 inline animate-pulse w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                    </svg>
                                    <span class="bg-[#eeaaff44] border border-[#eeaaff] py-2 px-4 rounded-lg"><span id="exchange_rial">0</span> ریال</span>
                                </p>
                                <div class="font-sans text-lg font-semibold text-white flex flex-wrap gap-3 justify-between items-stretch">
                                    <input type="submit" name="type" value="gold_to_rial" id="sub_gold_to_rial" class="hidden peer" disabled="true">
                                    <label for="sub_gold_to_rial" class="bg-blue-500 py-2 px-4 rounded-lg peer-disabled:bg-gray-500 peer-disabled:cursor-not-allowed whitespace-nowrap enabled:hover:opacity-90 active:scale-[0.95]">تبدیل طلا به ریال</label>
                                    <input type="submit" name="type" id="sub_rial_to_gold" value="rial_to_gold" class="hidden peer" disabled="true">
                                    <label for="sub_rial_to_gold" class="bg-blue-500 py-2 px-4 rounded-lg peer-disabled:bg-gray-500 peer-disabled:cursor-not-allowed whitespace-nowrap enabled:hover:opacity-90 active:scale-[0.95]">تبدیل ریال به طلا</label>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="w-full mx-auto lg:w-1/3 max-h-min">
                    <div class="block w-full h-full bg-white/20 rounded-lg border border-white/25 py-3 px-4 flex flex-col">
                        <h1 class="font-bold text-2xl text-center border-b border-white/30 pb-4">مبادلات انجام شده</h1>
                        <div class="block h-full w-full py-4">
                            <div id="table_parent" class="h-full w-full overflow-y-auto px-1">
                                <table id="table" class="hidden w-full table-auto table" dir="ltr">
                                    <thead>
                                        <tr class="border-b border-white/20 bg-white/30 backdrop-blur w-full sticky top-0">
                                            <th class="text-center py-2  rounded-tl-lg">تاریخ</th>
                                            <th class="text-center py-2 ">قیمت واحد</th>
                                            <th class="text-center py-2 ">دریافت</th>
                                            <th class="text-center py-2 ">پرداخت</th>
                                            <th class="text-center py-2  rounded-tr-lg">مبادله</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="py-1 px-4 bg-black/30 text-gray-400 text-center">نسخه آزمایشی 1.0.0</div>

        <div class="fixed pointer-events-none max-w-screen left-1/2 bottom-0 p-3 -translate-x-1/2 h-fit w-full md:w-1/2 lg:w-fit md:left-auto md:right-0 md:translate-x-0 lg:max-w-[35%] flex flex-col gap-3">

            @foreach ($errors->all() as $message)
                <div class="remove-me bg-red-700/40 p-4 alert backdrop-blur rounded-lg text-white font-bold flex gap-3 items-center tranition-all duration-300">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>                      
                    </div>
                    <div class="grow border-r border-red-300/50 pr-3">{{ $message }}</div>
                </div>
            @endforeach

            @if ($showSuccessMessage)
                <div class="remove-me bg-green-700/40 p-4 alert backdrop-blur rounded-lg text-white font-bold flex gap-3 items-center tranition-all duration-300">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>                      
                    </div>
                    <div class="grow border-r border-green-300/50 pr-3">{{ $showSuccessMessage }}</div>
                </div>
            @endif

        </div>

        <script>
            window.onload = () => {
                const logs = JSON.parse(@json($logs));
                const rial_amount = {{ $rial_amount }};
                const gold_amount = {{ $gold_amount }};
                const tether_amount = 0;
                const gold_price = {{ $gold_price }};

                const date_format = new Intl.DateTimeFormat("fa-IR-u-nu-latn",{
                    year:"numeric",
                    month:"2-digit",
                    day:"2-digit",
                    hour:"2-digit",
                    minute:"2-digit"
                });

                const _rial_amount = document.getElementById('rial_amount');
                const _gold_amount = document.getElementById('gold_amount');
                const _tether_amount = document.getElementById('tether_amount');
                const _gold_price = document.getElementById('gold_price');
                const _withdraw_range = document.querySelector('#withdraw input[name="amount"]');
                const _sub_sellected = document.getElementById('sub_sellected');
                const _withdraw_rial = document.getElementById('withdraw_rial');

                const _exchange_range = document.querySelector('#exchange input[name="amount"]');
                const _exchange_gold = document.getElementById('exchange_gold');
                const _exchange_rial = document.getElementById('exchange_rial');
                const _sub_rial_to_gold = document.getElementById('sub_rial_to_gold');
                const _sub_gold_to_rial = document.getElementById('sub_gold_to_rial');

                const _table_parent = document.getElementById('table_parent');
                const _table = document.getElementById('table');
                const _table_body = _table.querySelector('tbody');

                const _alerts = document.querySelectorAll('.remove-me');

                _alerts.forEach((v,k)=>{
                    setTimeout(()=>{
                        v.style.opacity = '0';
                        setTimeout(()=>{
                            v.style.display = 'none';
                        }, 350)
                    }, 3500 + (2000 * k));
                })

                _withdraw_range.oninput = (e) => {
                    _withdraw_rial.innerText = Number(e.target.value).toLocaleString();
                    if(e.target.value > 0)
                        _sub_sellected.disabled = false;
                    else
                        _sub_sellected.disabled = true;
                }

                _exchange_range.oninput = (e) => {
                    _exchange_gold.innerText =  Number(e.target.value).toLocaleString();
                    _exchange_rial.innerText = Number(e.target.value * gold_price).toLocaleString();
                    if(e.target.value > 0)
                    {
                        _sub_rial_to_gold.disabled = false;
                        _sub_gold_to_rial.disabled = false;
                    }
                    else
                    {
                        _sub_rial_to_gold.disabled = true;
                        _sub_gold_to_rial.disabled = true;
                    }
                }
                
                setTimeout(()=>{
                    _rial_amount.innerText = Number(rial_amount).toLocaleString();
                    _gold_amount.innerText = Number(gold_amount).toLocaleString();
                    _tether_amount.innerText = Number(tether_amount).toLocaleString();
                    _gold_price.innerText = Number(gold_price).toLocaleString();
                    _withdraw_range.max = rial_amount;
                    let avilable_gold = parseInt(rial_amount / gold_price);
                    _exchange_range.max = avilable_gold;

                    // fill table
                    logs.forEach((log, i)=>
                    {
                        _table_body.innerHTML += `
                            <tr id="TB_` + i + `" class="even:bg-black/10 odd:bg-white/10 tbtr" style="transition: all 1s; opacity:0;">
                                <td class="p-2 text-center font-mono text-sm">` + (date_format.format(new Date(log.created_at))) + `</td>
                                <td class="p-2 text-center font-mono text-sm">` + Number(log.unit_price).toLocaleString() + ` Rials</td>
                                <td class="p-2 text-center font-mono text-sm">` + Number(log.received_amount).toLocaleString() + ` ` + (log.exchange_type == 'rial_to_gold' ? 'Gram(s)' : 'Rials') + `</td>
                                <td class="p-2 text-center font-mono text-sm">` + Number(log.given_amount).toLocaleString() + ` ` + (log.exchange_type == 'rial_to_gold' ? 'Rials' : 'Gram(s)') + `</td>
                                <td class="p-2 text-center whitespace-nowrap">` + (log.exchange_type == 'rial_to_gold' ? 'ریال به طلا' : 'طلا به ریال') + `</td>
                            </tr>`;
                    });
                    
                    setTimeout(()=>{
                        logs.forEach((log, i)=>
                        {
                            setTimeout(()=>{
                                document.getElementById('TB_' + i).style.opacity = 1;
                            }, 100 + 100*i);
                        });
                    },100)

                    

                }, 500);
                
                window.onresize = fix_table_height;
                
                fix_table_height();
                pointerScroll(_table_parent);

                function fix_table_height()
                {
                    if(window.innerWidth < 1024)
                    {
                        _table.style.display = 'table';
                        _table_parent.style.maxHeight = 'none';
                    }
                    else
                    {
                        _table.style.display = 'none';
                        _table_parent.style.maxHeight = 'none';
                        _table_parent.style.maxHeight = _table_parent.clientHeight + 'px';
                        _table.style.display = 'table';
                    }
                }

                function pointerScroll(e) {
                    e.style.touchAction = 'none';
                    e.style.userSelect = 'none';
                    let isDrag = false;
                    const toggleDrag = () => isDrag = !isDrag;
                    const drag = (ev) => isDrag && (e.scrollLeft -= ev.movementX) && (e.scrollTop -= ev.movementY);
                    e.addEventListener("pointerdown", () => isDrag = true);
                    addEventListener("pointerup", () => isDrag = false);
                    addEventListener("pointermove", drag);
                };
            }
        </script>
    </body>
</html>