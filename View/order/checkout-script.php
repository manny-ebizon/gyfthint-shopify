<script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=USD"></script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script type="text/javascript">              
  $(document).ready(function(){    
    $("input[name=payment_method]").change(function(){
      if ($(this).val()=="paypal") {
        $("#orderForm button[type=submit]").addClass("d-none");
        $("#razorpay-button-container").addClass("d-none");
        $("#loading-div").addClass("d-none");
        $("#paypal-button-container").removeClass("d-none");   
        $("#paypal-button-container").html("");

        var form = $('#orderForm')[0];
        if (form.checkValidity()) {
            paypal.Buttons({
              style: {
                  layout: 'vertical',
                  color:  'gold',
                  shape:  'rect',
                  label:  'pay'
              },
              createOrder: function(data, actions) {
                  return actions.order.create({
                      purchase_units: [{
                          amount: {
                              value: summary_update(true), // Replace with the amount you want to charge
                              currency: 'USD'
                          }
                      }],
                      intent: "CAPTURE"
                  });
              },
              onApprove: function(data, actions) {
                  return actions.order.capture().then(function(details) {
                    $("#paypal-button-container").html("<i class='bx bx-loader-circle bx-spin' style='font-size:30px;'></i>");
                      $.post("/api/add-order",{"paymentID":data.paymentID,"token":localStorage.getItem("token"),"data":$("#orderForm").serialize()},function(response){
                          var result = JSON.parse(response);
                          if (result.status) {
                            toastr.success(result.message, 'Good Job!')
                            $("#orderForm")[0].reset();
                            setTimeout(function(){
                              if ($("#orderForm input[name=service]").val()=="blogger outreach") {
                                window.location = "/order/current-orders/";
                              } else {
                                window.location = "/order/current-orders/others";
                              }
                            },2000);
                          } else {
                            toastr.error(result.message, 'Oops!')
                          }
                        })
                  });
              }
            }).render('#paypal-button-container');  
        } else {          
            form.reportValidity();
            $(this).prop('checked', false);
        }        

      } else if ($(this).val()=="pay_later") {
        $("#paypal-button-container").addClass("d-none");        
        $("#razorpay-button-container").addClass("d-none")
        $("#loading-div").addClass("d-none");
        $("#orderForm button[type=submit]").removeClass("d-none");
      } else {
        $("#paypal-button-container").addClass("d-none");        
        $("#loading-div").removeClass("d-none");
        $("#orderForm button[type=submit]").addClass("d-none");
        $.post("/api/generate-razorpay-id",{"amount":summary_update(true)*100,"token":localStorage.getItem("token"),"data":$("#orderForm").serialize()},function(response){
          var result = JSON.parse(response);
          if (result.status) {            
            $("#paypal-button-container").addClass("d-none");
            $("#loading-div").addClass("d-none");
            $("#razorpay-button-container").removeClass("d-none")

            var options = {
                "key": "<?php echo RAZORPAY_API_KEY; ?>",
                "amount": summary_update(true)*100,
                "currency": "USD",
                "name": "Creative Brains for Design and Marketing",
                "description": $("#orderForm input[name=service]").val().toUpperCase(),
                "order_id": result.razorpay_id, // Pass the `id` obtained in the previous step
                "handler": function (response){
                  $.post("/api/add-order",{"razorpay_signature":response.razorpay_signature,"razorpay_order_id":response.razorpay_order_id,"paymentID":response.razorpay_payment_id,"token":localStorage.getItem("token"),"data":$("#orderForm").serialize()},function(response){
                      var result = JSON.parse(response);
                      if (result.status) {
                        toastr.success(result.message, 'Good Job!')
                        $("#orderForm")[0].reset();
                        setTimeout(function(){
                          if ($("#orderForm input[name=service]").val()=="blogger outreach") {
                            window.location = "/order/current-orders/";
                          } else {
                            window.location = "/order/current-orders/others";
                          }
                        },2000);
                      } else {
                        toastr.error(result.message, 'Oops!')
                      }
                    })                                        
                }
            };

            var rzp1 = new Razorpay(options);
            document.getElementById('razorpayBtn').onclick = function(e){
                rzp1.open();
                e.preventDefault();
            }
          } else {
            toastr.error(result.message, 'Oops!')
            $("#card-payment-method").prop("checked",false);
            $("#loading-div").addClass("d-none");
          }
        })
      }
    })

    function updateRazorpay() {
      $("#loading-div").removeClass("d-none");
      $("#orderForm button[type=submit]").addClass("d-none");
      $("#paypal-button-container").addClass("d-none");
      $("#razorpay-button-container").addClass("d-none")
      $.post("/api/generate-razorpay-id",{"amount":summary_update(true)*100,"token":localStorage.getItem("token"),"data":$("#orderForm").serialize()},function(response){
        var result = JSON.parse(response);
        if (result.status) {            
          $("#paypal-button-container").addClass("d-none");
          $("#loading-div").addClass("d-none");
          $("#razorpay-button-container").removeClass("d-none")

          var options = {
              "key": "<?php echo RAZORPAY_API_KEY; ?>",
              "amount": summary_update(true)*100,
              "currency": "USD",
              "name": "Creative Brains for Design and Marketing",
              "description": $("#orderForm input[name=service]").val().toUpperCase(),
              "order_id": result.razorpay_id, // Pass the `id` obtained in the previous step
              "handler": function (response){
                $.post("/api/add-order",{"razorpay_signature":response.razorpay_signature,"razorpay_order_id":response.razorpay_order_id,"paymentID":response.razorpay_payment_id,"token":localStorage.getItem("token"),"data":$("#orderForm").serialize()},function(response){
                    var result = JSON.parse(response);
                    if (result.status) {
                      toastr.success(result.message, 'Good Job!')
                      $("#orderForm")[0].reset();
                      setTimeout(function(){
                        if ($("#orderForm input[name=service]").val()=="blogger outreach") {
                          window.location = "/order/current-orders/";
                        } else {
                          window.location = "/order/current-orders/others";
                        }
                      },2000);
                    } else {
                      toastr.error(result.message, 'Oops!')
                    }
                  })                                        
              }
          };

          var rzp1 = new Razorpay(options);
          document.getElementById('razorpayBtn').onclick = function(e){
              rzp1.open();
              e.preventDefault();
          }
        } else {
          toastr.error(result.message, 'Oops!')
          $("#card-payment-method").prop("checked",false);
          $("#loading-div").addClass("d-none");
        }
      })
    }
    
    $(document).on("change", ".packages", (function(e){
      e.preventDefault();
      e.stopImmediatePropagation();
      summary_update();
    })) 

    function fetchDetails(){
      var id = '<?php echo $_SESSION['service_uuid']; ?>';
      $.post("/api/fetch-data/",{"id":id,"token":localStorage.getItem("token"),"t":"services"},function(response){
        var result = JSON.parse(response);
        console.log(result);
        if (result.status) {
          $('#quantity').html("");
          //$('#quantity').html('<input type="text" id="quantityInput" class="form-control packages" value="1" step="1" name="quantity" data-price="'+result.data.service_price+'" data-name="Quantity"/>');
          //$('#quantity').html('<input type="text" id="service_type" class="form-control packages" name="service_type" value="'+result.data.service_type+'" data-name="Quantity"/>');
          $("#service_details").html("");
          $("#service_details").html('<label><b>Service Name:</b> '+result.data.service_name+'</label><label><b>Service Description:</b> '+result.data.service_description+'</label><label class="price"><b>Price:</b> $'+result.data.service_price+'</label><p><label class="service_type"><b>Term: </b>'+result.data.service_type+'</label></p>');
          $("#service").val(result.data.service_name);
          $("#price").val(result.data.service_price);
          $("#service_type").val(result.data.service_type);
          summary_update();
        } else {
          toastr.error(result.message, 'Oops!')
        }
      })
    }
    fetchDetails();

    function summary_update($returnvalue=false) {
      var total = subtotal = discount = 0;
      if ($returnvalue==false) {
        $("#summary-details tbody").html('');  
      }

      /*
      $(".packages").each(function(){
        if ($(this).val()>0) {
          var amount = $(this).data("price") * $(this).val();
          subtotal += amount;
          if ($returnvalue==false) {
            $("#summary-details tbody").append('<tr style="border-bottom:1px dotted"><td class="ps-0">'+$(this).data("name")+'</td><td class="text-right pe-0" style="width:25%">$'+amount.toFixed(2)+'</td></tr>');
          }
        }
      })
      */
     
      var amount = $("#price").val();
      subtotal += amount;
      if ($returnvalue==false) {
        $("#summary-details tbody").append('<tr style="border-bottom:1px dotted"><td class="ps-0">'+$("#service").val()+'</td><td class="text-right pe-0" style="width:25%">$'+parseFloat(amount).toFixed(2)+'</td></tr>');
      }

      if ($returnvalue==false) {
        if (subtotal>0) {
          $("#coupon-box").removeClass("d-none")
        } else {
          $("#coupon-box").addClass("d-none")
        }
        $("#subtotal").html(parseFloat(subtotal).toFixed(2));
      }

      if ($("input[name=coupon_type]").val()=="value") {
        discount = $("input[name=coupon_value]").val();       
        discount = parseFloat(discount).toFixed(2);
        if ($returnvalue==false) {
          $("#discount").html(discount);
        }
      } else if ($("input[name=coupon_type]").val()=="percentage") {
        discount = subtotal * ($("input[name=coupon_value]").val()/100);
        if ($returnvalue==false) {
          $("#discount").html(discount.toFixed(2));
        }
      }
      
      total = subtotal - discount;
      if ($returnvalue==false) {
        $("#total").html(total.toFixed(2));
        if (total>0) {
          $("#summaryBox").removeClass("d-none");                    
        } else {
          $("#summaryBox").addClass("d-none");
        }
      }

      if ($returnvalue==false && $("input[name='payment_method']:checked").val()=="razorpay") {
        updateRazorpay()  
      }
      if ($returnvalue==true) {
        return total.toFixed(2);  
      }      
    }

    $("#orderForm").submit(function(e){
      e.preventDefault();                  
      var $this = this;
      var button_text = $("button[type=submit]",this).html();
      $("button[type=submit]",$this).attr("disabled","disabled");                
      $("button[type=submit]",$this).html(loading_text);
      $.post("/api/add-order",{"token":localStorage.getItem("token"),"data":$(this).serialize()},function(response){
        $("button[type=submit]",$this).removeAttr("disabled");
        $("button[type=submit]",$this).html(button_text);        
        var result = JSON.parse(response);
        if (result.status) {
          toastr.success(result.message, 'Good Job!')
          $("#orderForm")[0].reset();
          setTimeout(function(){
            if (result.order_id!=undefined) {
              window.location = "/order/"+result.order_id;
            } else {
              if ($("#orderForm input[name=service]").val()=="blogger outreach") {
                window.location = "/order/current-orders/";
              } else {
                window.location = "/order/current-orders/others";
              }  
            }                      
          },2000);
        } else {
          toastr.error(result.message, 'Oops!')
        }
      })
    })

    $(".checkCouponBtn").click(function(){
      var coupon = $("input[name=coupon_code]").val();
      if (coupon=="") {
        return;
      }
      var $this = this;
      var button_text = $($this).html();
      $($this).attr("disabled","disabled");
      $($this).html(loading_text);
      $.post("/api/check-coupon/",{"token":localStorage.getItem("token"),"coupon":coupon},function(response){
        $($this).removeAttr("disabled");
        $($this).html(button_text);
        var result = JSON.parse(response);
        if (result.status) {
          $("input[name=coupon_value]").val(result.coupon_value);
          $("input[name=coupon_type]").val(result.coupon_type)
          $("input[name=coupon]").val(result.coupon_id)
          $("input[name=coupon_code]").addClass("border-success");          
          summary_update()
        } else {
          toastr.error(result.message, 'Oops!')
        }
      })
    })

  })          
</script> 