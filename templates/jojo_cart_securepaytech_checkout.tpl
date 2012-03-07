<form name="securepaytech_form" id="securepaytech_form" method="post" action="{$SECUREURL}/cart/process/{$token}/">
<div class="box contact-form">
    <h2>Pay by credit card</h2>
    <input type="hidden" name="token" id="token" value="{$token}" />
    <input type="hidden" name="paymentmethod" value="dps" />{* this line probably not needed *}
    <input type="hidden" name="handler" value="securepaytech" />
    
    <label for="securepaytech_cardType">Card Type:</label>
    <select name="cardType" id="securepaytech_cardType">
      <option value="">Select card type</option>
      {section name=c loop=$cardtypes}
      <option value="{$cardtypes[c]}"{if ($fields.cardType==$cardtypes[c] && $OPTIONS.cart_test_mode!='yes') || ($OPTIONS.cart_test_mode=='yes' && $cardtypes[c]|strtolower=='visa')} selected="selected"{/if}>{$cardtypes[c]|ucfirst}</option>
      {/section}
    </select><br />
    
    <label for="securepaytech_cardNumber">Card Number:</label>
    <input type="text" size="30" maxlength="19" name="cardNumber" id="securepaytech_cardNumber" value="{if $OPTIONS.cart_test_mode=='yes'}4987 6543 2109 8769{/if}" autocomplete="off" /><br />
    
    <label for="securepaytech_cardExpiryMonth">Expiry Date:</label>
    <div class="form-field">
    <input type="text" size="2" maxlength="2" name="cardExpiryMonth" id="securepaytech_cardExpiryMonth" value="{if $OPTIONS.cart_test_mode=='yes'}05{/if}" autocomplete="off" /> / <input type="text" size="2" maxlength="2" name="cardExpiryYear" id="securepaytech_cardExpiryYear" value="{if $OPTIONS.cart_test_mode=='yes'}13{/if}" /> (mm/yy)
    </div><br />
    
    <label for="securepaytech_cardName">Name on card</label>
    <input type="text" size="30" name="cardName" id="securepaytech_cardName" value="{if $OPTIONS.cart_test_mode=='yes'}Test Cardholder{/if}" autocomplete="off" /><br />
    {if $OPTIONS.securepaytech_use_csc=='yes'}
    <label for="securepaytech_CSC">Card Security Code</label>
    <input type="text" size="5" name="CSC" id="securepaytech_CSC" value="{if $OPTIONS.cart_test_mode=='yes'}100{/if}" autocomplete="off" /><br />
    {/if}
    
  </div>

<div style="text-align: center;"><input type="image" src="images/btn-pay-now.gif" name="pay" id="pay" value="Pay by Credit card" onclick="if (validateSecurePayTech()){ldelim}$('#securepaytech_pay').attr('disabled',true);$('#securepaytech_form').submit();{rdelim}else{ldelim}return false;{rdelim}" /></div>

</form>
<div style="text-align:center">
  <a href="http://www.securepaytech.com" rel="nofollow" target="_BLANK"><img src="images/powered-by-securepaytech.gif" alt="Powered by SecurePayTech" /></a>
  <img src="images/{if $OPTIONS.cart_test_mode=='yes' || !$OPTIONS.securepaytech_merchant_id}paymark-not-certified.png{else}paymark.png{/if}" alt="Paymark certified" onclick="paymark_verify('{$OPTIONS.securepaytech_merchant_id}');" />
</div>

<script type="text/javascript">
{literal}
function validateSecurePayTech() {
  /* check card type is selected */  
  if ($('#securepaytech_cardType').val() == '') {
    alert('Please enter the card type');
    return false;
  }
  /* check card format */  
  if (!checkCreditCard($('#securepaytech_cardNumber').val(), $('#securepaytech_cardType').val())) {
    alert('Please enter a valid credit card number');
    return false;
  }
  /* check expiry date is not empty */  
  if (($('#securepaytech_cardExpiryMonth').val() == '') || ($('#securepaytech_cardExpiryYear').val() == '')) {
    alert('Please enter an expiry date');
    return false;
  }
  /* check card name is not empty */  
  if ($('#securepaytech_cardName').val() == '') {
    alert('Please enter the name on the card');
    return false;
  }
  return true;
}

function paymark_verify(merchant) {
  window.open('http://www.paymark.co.nz/dart/darthttp.dll?etsl&tn=verify&merchantid='+merchant,'verify', 'scrollbars=yes, width=400, height=400');
}
</script>
{/literal}
</script>