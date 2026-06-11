<div class="container-fluid px-4">



    <style>
        body {

            font-family: "Roboto", sans-serif;

            font-optical-sizing: auto;

            font-weight: 400;

            font-style: normal;

            font-variation-settings: "wdth" 100;

        }



        table {

            width: 100%;

            height: auto;

            border-collapse: collapse;

        }





        .table-container {

            display: flex;

            flex-direction: column;

            height: 100vh;

            position: relative;

        }



        .table-container table {

            flex-grow: 1;

            width: 100%;

            border-collapse: collapse;



        }



        .footer {

            position: absolute;

            bottom: 0;

            left: 0;

            width: 100%;

            height: 75px;

            display: flex;

            flex-direction: column;

            justify-content: flex-end;

            align-items: flex-end;



        }



        html,

        body {

            height: 100%;

        }







        h4,

        h1,

        h5,

        h2,

        h3,

        h6,

        p {

            margin-top: 0;

            color: #000;

        }

        .payment_receipt {

            display: none;

        }

        .payment_receipt.visible {

            display: block;

        }



        @media print {



            .print-hide-admin,

            .print-btn,

            .btn {

                display: none !important;

            }


            .form-control {

                border: none !important;

                padding: 0;

                text-align: left;

            }



            .form-control::placeholder {

                opacity: 0;

            }

        }



        .logo-place {
            text-align: center;
            margin-bottom: 21px;
        }

        .invoice-number {
            text-align: center;
            font-size: 17px;
            color: #000;
        }

        .contact-list {
            display: block;
            list-style: none;
        }

        .contact-list li {
            display: flex;
            align-items: flex-start;
            position: relative;
            padding: 0 0 0 22px;
            color: #000;
            margin-bottom: 5px;
        }

        .contact-list>li img {
            width: 16px;
            line-height: 1;
            margin-right: 8px;
            position: absolute;
            top: 7px;
            left: 0;
        }

        .line {
            background: #eeeeee;
            height: 1px;
        }

        .qty-style button {
            margin-bottom: 0;
            padding: 8px 15px;
            border-radius: 0;
            background: #344767;
            border: 1px solid #344767;
            color: #fff;
        }

        .qty-style input {
            background: #fff !important;
            border: 1px solid #344767 !important;
            max-width: 60px !important;
            flex: 0 0 60px !important;
        }

        .loop span {
            font-size: 12px;
            font-weight: 500;
        }

        .border-custom {
            border-top: 1px solid #e4e0e0;
        }

        .big-label {
            font-size: 13px;
        }
    </style>







    <div class="table-container">


        <div class="card-header p-4">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="logo-place">
                        <img src="{{asset('assets/img/pdf_logo.png')}}" style="width:280px; height:auto;">
                    </div>

                </div>
                <div class="col-md-6">
                    <ul class="contact-list">
                        <li>
                            <img src="{{asset('assets/img/phone.svg')}}" alt="">
                            <span style="display:inline-block;"> (+242) 05 554 7777 </span>
                        </li>

                        <li>
                            <img src="{{asset('assets/img/phone.svg')}}" alt="">
                            <span style="display:inline-block;"> (+242) 05 582 5555 </span>
                        </li>

                        <li>
                            <img src="{{asset('assets/img/mail.svg')}}" alt="">
                            <span style="display:inline-block;"> contact@stannys.com </span>
                        </li>

                        <li>
                            <img src="{{ asset('assets/img/globe.svg')}}" alt="">
                            <span style="display:inline-block;"> www.stannys.com </span>
                        </li>

                        <li>
                            <img src="{{asset('assets/img/map-pin.svg')}}" alt="">
                            <span style="display:inline-block;"> 18, Blv. Denis Sassou N'guesso,<br> Brazzaville,
                                République du Congo </span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="row">


                <div class="col-md-4 position-relative">
                    <label class="date_lable">Customer Name:</label>
                    {{-- <input type="text" name="customer_name" class="form-control border border-2 p-2"
                        wire:model="customer_name" required> --}}
                    <input type="text" class="form-control form-control-sm border border-1 customer_input"
                        placeholder="Enter Customer Name" wire:model="customer_name">
                    @error('customer_name')
                    <p class="text-danger">{{$message}}</p>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="date_lable">Mobile:</label>
                    <div class="extention-group">
                        <select wire:model="selectedCountryPhone"
                            wire:change="GetCountryDetails($event.target.value, 'phone')"
                            class="form-control form-control-sm">
                            <option value="" selected hidden>Select Country</option>
                            @foreach($countries as $country)
                            <option value="{{ $country->country_code }}">{{
                                $country->title }} ({{ $country->country_code
                                }})</option>
                            @endforeach
                        </select>
                        <input type="text" class="form-control form-control-sm border border-2 p-2" wire:model="mobile"
                            placeholder="Enter Mobile Number" maxlength="{{$mobileLengthPhone}}">
                    </div>

                    @error('mobile')
                    <p class="text-danger">{{$message}}</p>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="date_lable">Email:</label>

                    <input type="email" class="form-control form-control-sm border border-2 p-2" wire:model="email"
                        placeholder="Enter Email Address">

                    @error('email')
                    <p class="text-danger">{{$message}}</p>
                    @enderror
                </div>


                <div class="col-md-4">
                    <label class="date_lable">Address:</label>

                    <textarea class="form-control form-control-sm border border-2 p-2" wire:model="address"
                        placeholder="Enter Address"></textarea>

                    @error('address')
                    <p class="text-danger">{{$message}}</p>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="date_lable">Date:</label>

                    <input type="date" class="form-control form-control-sm border border-2 p-2" wire:model="date">

                    @error('date')
                    <p class="text-danger">{{$message}}</p>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="date_lable">No:</label>

                    <input type="text" class="form-control form-control-sm border border-2 p-2"
                        wire:model="proforma_number" placeholder="Enter Proforma Number">

                    @error('proforma_number')
                    <p class="text-danger">{{$message}}</p>
                    @enderror
                </div>


            </div>

            <div class="line mt-5 mb-5"></div>

            <div class="loop-holder mb-5">
                @foreach ($rows as $index => $row)
                <div class="loop">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="date_lable">Description</label>
                            <select class="form-control product-select" wire:model="rows.{{ $index }}.product_id">

                                <option value="" selected hidden>Select Product</option>

                                @foreach ($products as $product)
                                <option value="{{$product['id']}}">{{$product['name']}}</option>
                                @endforeach

                            </select>

                            @error('rows.'.$index.'.product_id')
                            <p class="text-danger">{{$message}}</p>
                            @enderror
                        </div>

                        <div class="col-md-2">
                            <label class="date_lable">Quantity</label>
                            <div class="input-group qty-style">

                                <div class="input-group-prepend">

                                    <button class="btn btn-outline-secondary btn-qty minus" type="button"
                                        wire:click="updateQuantity({{ $index }}, 'decrease')">-</button>

                                </div>

                                <input type="text" class="form-control quantity text-center" value="1" min="1"
                                    wire:model="rows.{{ $index }}.quantity" readonly>

                                <div class="input-group-append">

                                    <button class="btn btn-outline-secondary btn-qty plus" type="button"
                                        wire:click="updateQuantity({{ $index }}, 'increase')">+</button>

                                </div>

                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="date_lable">Unit Price</label>
                            <input type="text" class="form-control amount"
                                wire:model.lazy="rows.{{ $index }}.unit_price" wire:blur="updatePrice({{ $index }})"
                                placeholder="Enter Amount">

                            @error('rows.'.$index.'.unit_price')

                            <p class="text-danger">{{$message}}</p>

                            @enderror
                        </div>
                        <div class="col-md-2 align-self-end">
                            <label class="date_lable">Total Price</label>
                            <input type="text" class="form-control amount" value="0" readonly
                                wire:model="rows.{{ $index }}.total">
                            <span>FCFA</span>
                        </div>
                        <div class="col-md-2 align-self-center">
                            <button type="button" class="btn btn-danger btn-sm remove-row mb-0"
                                wire:click="removeRow({{$index}})"><i class="fas fa-trash-alt"></i></button>
                            <button class="btn btn-success btn-sm mb-0" wire:click="addRow"><i
                                    class="fas fa-plus"></i></button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="row mb-4">
                <div class="col-md-6"></div>

                <div class="col-md-6">
                    <div class="row">
                        @php

                        $subtotal = collect($rows)->sum(function ($row) {

                        return floatval($row['total']);

                        });

                        $tva = $subtotal * 0.18;

                        $ca = $tva * 0.05;

                        $ht_amount = $subtotal - ($tva + $ca);

                        @endphp
                        <div class="col-md-6 text-left p-2 border-custom">
                            <div class="date_lable big-label"><strong>Total</strong></div>
                        </div>
                        <div class="col-md-6 text-right p-2 border-custom">
                            <div class="date_lable text-end big-label"> {{$subtotal}} FCFA</div>
                        </div>

                        <div class="col-md-6 text-left p-2 border-custom">
                            <div class="date_lable big-label">H.T</div>
                        </div>
                        <div class="col-md-6 text-right p-2 border-custom">
                            <div class="date_lable text-end big-label">{{ number_format($ht_amount, 2) }} FCFA</div>
                        </div>

                        <div class="col-md-6 text-left p-2 border-custom">
                            <div class="date_lable big-label">T.V.A</div>
                        </div>
                        <div class="col-md-6 text-right p-2 border-custom">
                            <div class="date_lable text-end big-label">{{ number_format($tva, 2) }} FCFA</div>
                        </div>

                        <div class="col-md-6 text-left p-2 border-custom">
                            <div class="date_lable mb-4 big-label">C.A</div>
                        </div>
                        <div class="col-md-6 text-right p-2 border-custom">
                            <div class="date_lable text-end mb-4 big-label">
                                {{ number_format($ca, 2) }} FCFA
                            </div>
                        </div>

                        <div class="col-md-6 text-left p-2 border-custom">
                            <div class="date_lable big-label"><strong>Total</strong></div>
                        </div>
                        <div class="col-md-6 text-right p-2 border-custom">
                            <div class="date_lable text-end big-label">{{$subtotal}} FCFA</div>
                        </div>
                    </div>

                    


                </div>
                <label for="">Condition </label>
                <div wire:ignore>
                    <textarea id="condition" cols="" rows="" wire:model="condition"></textarea>
                </div>

                @error('condition')
                <p class="text-danger">{{$message}}</p>
                @enderror
                <div class="row">
                    <div class="col-12 text-end">
                        <button id="terget" class="btn btn-primary print-btn btn-sm mt-5"
                            wire:click.prevent="generateProforma">GENERATE PROFORMA</button>
                    </div>
                </div>
            </div>


            <div class="row ">
                <div class="col-md-12 text-center">
                    <h6>STE RADHEY'S SARL</h6>
                    <div class="line mt-2 mb-2"></div>
                    <div class="">
                        CAPITAL: 1.000.000 FCFA<br>
                        NIU M24000000659298E<br>
                        RCCM: CG-BZV-01-2025-B20-00004
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script src="https://cdn.ckeditor.com/ckeditor5/38.1.0/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
                // ClassicEditor
                //     .create(document.querySelector('#condition'))
                //     .catch(error => {
                //         console.error(error);
                //     });
                ClassicEditor
                    .create(document.querySelector('#condition'))
                    .then(editor => {
                        editor.model.document.on('change:data', () => {
                            @this.set('condition', editor.getData());
                        });
                    })
                    .catch(error => {
                        console.error(error);
                    });
                });
            
    </script>
</div>