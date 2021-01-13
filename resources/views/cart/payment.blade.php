<div class="card card-profile shadow mt--300">
    <div class="px-4">
      <div class="mt-5">
        <h3>{{ __('Checkout') }}<span class="font-weight-light"></span></h3>
      </div>
      <div  class="border-top">
        <!-- Price overview -->
        <div id="totalPrices" v-cloak>
            <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <span v-if="totalPrice==0">{{ __('Cart is empty') }}!</span>

                            <span v-if="totalPrice"><strong>{{ __('Subtotal') }}:</strong></span>
                            <span v-if="totalPrice" class="ammount"><strong>@{{ totalPriceFormat }}</strong></span>
                            @if(config('app.isft'))
                                <span v-if="totalPrice&&delivery"><br /><strong>{{ __('Delivery') }}:</strong></span>
                                <span v-if="totalPrice&&delivery" class="ammount"><strong>@{{ deliveryPriceFormated }}</strong></span><br />
                            @endif
                            <br />
                            <span v-if="totalPrice"><strong>{{ __('TOTAL') }}:</strong></span>
                            <span v-if="totalPrice" class="ammount"><strong>@{{ withDeliveryFormat   }}</strong></span>
                            <input v-if="totalPrice" type="hidden" id="tootalPricewithDeliveryRaw" :value="withDelivery" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End price overview -->

        <!-- Payment  Methods -->
        <div class="cards">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <!-- Errors on Stripe -->
                        @if (session('error'))
                            <div role="alert" class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        @if(!env('IS_WHATSAPP_ORDERING_MODE',false) && !env('WHATSAPP_ORDERING_ENABLED',false) && !env('ENABLE_FACEBOOK_ORDERING',false) && !env('IS_FACEBOOK_ORDERING_MODE',false))
                        <!-- COD -->
                        @if (!env('HIDE_COD',false))
                            <div class="custom-control custom-radio mb-3">
                                <input name="paymentType" class="custom-control-input" id="cashOnDelivery" type="radio" value="cod" {{ env('DEFAULT_PAYMENT','cod')=="cod"?"checked":""}}>
                                <label class="custom-control-label" for="cashOnDelivery"><span class="delTime">{{ config('app.isqrsaas')?__('Cash / Card Terminal'): __('Cash on delivery') }}</span> <span class="picTime">{{ __('Cash on pickup') }}</span></label>
                            </div>
                        @endif

                        <!-- STIPE CART -->
                        @if (env('STRIPE_KEY',false)&&env('ENABLE_STRIPE',false))
                            <div class="custom-control custom-radio mb-3">
                                <input name="paymentType" class="custom-control-input" id="paymentStripe" type="radio" value="stripe" {{ env('DEFAULT_PAYMENT','cod')=="stripe"?"checked":""}}>
                                <label class="custom-control-label" for="paymentStripe">{{ __('Pay with card') }}</label>
                            </div>
                        @endif

                        <!-- PayPal -->
                        @if (env('PAYPAL_SECRET',false)&&env('ENABLE_PAYPAL',false))
                            <div class="custom-control custom-radio mb-3">
                                <input name="paymentType" class="custom-control-input" id="paymentPayPal" type="radio" value="paypal" {{ env('DEFAULT_PAYMENT','cod')=="paypal"?"checked":""}}>
                                <label class="custom-control-label" for="paymentPayPal">{{ __('Pay with PayPal') }}</label>
                            </div>
                        @endif

                        <!-- PAYFAST -->
                        @if(env('ENABLE_PAYSTACK', false))
                            <div class="custom-control custom-radio mb-3">
                                <input name="paymentType" class="custom-control-input" id="paymentPaystack" type="radio" value="paystack" {{ env('DEFAULT_PAYMENT','cod')=="paystack"?"checked":""}}>
                                <label class="custom-control-label" for="paymentPaystack">{{ __('Pay with Paystack') }}</label>
                            </div>
                        @endif

                        <!-- Mollie -->
                        @if(env('ENABLE_MOLLIE', false))
                            <div class="custom-control custom-radio mb-3">
                                <input name="paymentType" class="custom-control-input" id="paymentMollie" type="radio" value="mollie" {{ env('DEFAULT_PAYMENT','cod')=="mollie"?"checked":""}}>
                                <label class="custom-control-label" for="paymentMollie">{{ __('Pay with Mollie') }}</label>
                            </div>
                        @endif

                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- END Payment -->

        <!-- Payment Actions -->
        @if(!env('IS_WHATSAPP_ORDERING_MODE',false) && !env('WHATSAPP_ORDERING_ENABLED',false) && !env('ENABLE_FACEBOOK_ORDERING',false) && !env('IS_FACEBOOK_ORDERING_MODE',false))

            <!-- COD -->
            @include('cart.payments.cod')

            <!-- PayPal -->
            @if(env('ENABLE_PAYPAL',false))
                @include('cart.payments.paypal')
            @endif

            <!-- Paystack -->
            @if(env('ENABLE_PAYSTACK',false))
                @include('cart.payments.paystack')
            @endif

            <!-- Mollie -->
            @if(env('ENABLE_MOLLIE',false))
                @include('cart.payments.mollie')
            @endif

            </form>

            <!-- Stripe -->
            @include('cart.payments.stripe')

        @elseif(env('IS_WHATSAPP_ORDERING_MODE',false) || env('WHATSAPP_ORDERING_ENABLED',false))
            <div class="text-center">
                <button type="submit" class="btn btn-success mt-4 paymentbutton submit_btn">{{ __('Send Whatsapp Order') }}</button>
            </div>
        @elseif(env('IS_FACEBOOK_ORDERING_MODE',false) || env('ENABLE_FACEBOOK_ORDERING',false))
            <div class="text-center">
                <button type="button" id="fborder_btn" class="btn btn-success mt-4 paymentbutton">{{ __('Send Facebook Order') }}</button>
            </div>
        @endif
        <!-- END Payment Actions -->

        <br/><br/>
        <div class="text-center">
            <div class="custom-control custom-checkbox mb-3">
                <input class="custom-control-input" id="privacypolicy" type="checkbox">
                <label class="custom-control-label" for="privacypolicy">{{ __('I agree to the Terms and Conditions and Privacy Policy') }}</label>
            </div>
        </div>

      </div>
      <br />
      <br />
    </div>
  </div>

  @if(env('IS_DEMO', false) && env('ENABLE_STRIPE', false))
    @include('cart.democards')
  @endif
