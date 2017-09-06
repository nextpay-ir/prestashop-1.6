
<!-- Nextpay Payment Module -->
<p class="payment_module">
    <a href="javascript:$('#nextpay').submit();" title="{l s='Online payment with nextpay' mod='nextpay'}">
        <img src="modules/nextpay/nextpay.png" alt="{l s='Online payment with nextpay' mod='nextpay'}" />
		{l s=' پرداخت با کارتهای اعتباری / نقدی بانک های عضو شتاب توسط دروازه پرداخت نکست پی ' mod='nextpay'}
<br>
</a></p>

<form action="modules/nextpay/nx.php?do=payment" method="post" id="nextpay" class="hidden">
    <input type="hidden" name="orderId" value="{$orderId}" />
</form>
<br><br>
<!-- End of Nextpay Payment Module-->
