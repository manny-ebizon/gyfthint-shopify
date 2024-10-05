<!-- Payment Details -->
<div id="summaryBox" class="card">
  <h5 class="card-header pb-3">Summary</h5>
  <div class="card-body">
      <table id="summary-details" class="table" style="width:100%;">
        <tbody></tbody>
      </table>                            
      <div id="coupon-box" class="form-group d-none">
        <input type="hidden" name="coupon">
        <input type="hidden" name="coupon_value">
        <input type="hidden" name="coupon_type">
        <div class="clear20"></div>
        <label class="form-label">Coupon</label>
        <div class="row">
          <div class="col-6">
            <input type="text" name="coupon_code" class="form-control">
          </div>
          <div class="col-6 coupon-result">
            <button type="button" class="checkCouponBtn btn btn-info">Check Coupon</button>
          </div>
        </div>
      </div>
      <div class="clear30"></div>
      <p class="mb-0 text-right">Subtotal: $<span id="subtotal">0.00</span></p>
      <p class="mb-2 text-right">Discount: $<span id="discount">0.00</span></p>
      <h4 class="text-right"><b>Total: $<span id="total">0.00</span></b></h4>
      <div id="paymentCheckoutBox" class="mt-3">
        <label class="form-label">Select payment method:</label>
        <div class="clear5"></div>  
        <label for="card-payment-method" class="pointer"><input id="card-payment-method" type="radio" name="payment_method" value="razorpay" required> Credit Card (Razorpay)</label>
        <label for="paypal-payment-method" class="pointer"><input id="paypal-payment-method" class="ms-3" type="radio" name="payment_method" value="paypal" required> Paypal</label>
        <label for="paylater-payment-method" class="pointer"><input id="paylater-payment-method" class="ms-3" type="radio" name="payment_method" value="pay_later" required> Save Order Pay Later</label>
        <div class="clear20"></div>
        <div id="loading-div" class="d-none"><i class='bx bx-loader-circle bx-spin'></i></div>
        <button type="submit" class="btn btn-success me-2 d-none">Proceed Checkout</button>
        <div id="paypal-button-container" class="d-none text-center" style="max-width: 200px;"></div>
        <div id="razorpay-button-container" class="d-none">
          <button id="razorpayBtn" class="btn btn-success me-2" type="button">Pay with Razorpay</button>
        </div>
      </div>
  </div>
</div>
<!--/ Payment Details -->