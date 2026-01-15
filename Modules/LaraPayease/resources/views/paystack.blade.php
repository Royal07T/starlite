<html>
<head>
    <title>{{__('Paystack Payment Gateway')}}</title>
    <script src="https://js.paystack.co/v1/inline.js"></script>
</head>
<body>
<div class="paystack-payment-wrapper">
    <div class="paystack-payment-inner-wrapper">
        <input type="hidden" name="order_id" id="order_id_input" value="{{$paystack_data['order_id']}}"/>
        <form id="paystack_form">
            @foreach($paystack_data as $field_name => $value)
                <input type="hidden" name="{{$field_name}}" value="{{$value}}"/>
            @endforeach
        </form>
        <div class="btn-wrapper">
            <button id="payment_submit_btn"></button>
        </div>
    </div>
</div>

<script>
    (function(){
        "use strict";
        var orderID = document.getElementById('order_id_input').value;
        var submitBtn = document.getElementById('payment_submit_btn');

        document.addEventListener('DOMContentLoaded', function (){
            submitBtn.dispatchEvent(new Event('click'));
        }, false);

        submitBtn.addEventListener('click', function () {
            submitBtn.innerText = "{{__('Redirecting..')}}"
            submitBtn.disabled = true;
            var form = document.getElementById('paystack_form');
            var formData = new FormData(form);

            fetch("{{route('payease.paystack')}}", {
                headers: {
                    "X-CSRF-TOKEN" : "{{csrf_token()}}",
                },
                method: 'POST',
                processData: false,
                contentType: false,
                body: formData
            })
            .then(function (response) {
                return response.json();
            })
            .then(function (session) {
                if(session.hasOwnProperty('msg')){
                    alert(session.msg);
                    // Redirect to cancel page
                    window.location = document.querySelector('input[name="cancel_url"]').value;
                    return;
                }
                
                // Redirect to Paystack payment page
                if (session.authorization_url) {
                    window.location.href = session.authorization_url;
                } else {
                    // Initialize Paystack inline
                    var handler = PaystackPop.setup({
                        key: "{{$paystack_data['paystack_public_key']}}",
                        email: "{{$paystack_data['email']}}",
                        amount: {{$paystack_data['charge_amount']}},
                        currency: "{{$paystack_data['currency']}}",
                        ref: session.id,
                        callback: function(response) {
                            // Redirect to success URL with reference
                            window.location.href = "{{$paystack_data['ipn_url']}}" + "?reference=" + response.reference + "&order_id={{$paystack_data['order_id']}}";
                        },
                        onClose: function() {
                            // Redirect to cancel URL
                            window.location = document.querySelector('input[name="cancel_url"]').value;
                        }
                    });
                    handler.openIframe();
                }
            })
            .catch(function (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                window.location = document.querySelector('input[name="cancel_url"]').value;
            });
        });
    })();
</script>
</body>
</html>
