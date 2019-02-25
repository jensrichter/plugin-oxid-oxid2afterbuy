[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

<script type="text/javascript">
<!--
function _groupExp(el) {
    var _cur = el.parentNode;

    if (_cur.className == "exp") _cur.className = "";
      else _cur.className = "exp";
}

/**
 * Asks if sure to reset before performing operation
 */
function confirmReset() {
    var question = "[{oxmultilang ident="SHOP_MODULE_AFTERBUY_RESET_TRANSACTIONDATA_SURE"}]";
    var answer = confirm(question);
    if (answer == true) {
        document.myedit.fnc = "fcResetTransactionData";
        document.myedit.submit();
    }
}
//-->
</script>

[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
[{else}]
    [{assign var="readonly" value=""}]
[{/if}]

[{cycle assign="_clear_" values=",2" }]

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oViewConf->getActiveShopId()}]">
    <input type="hidden" name="cl" value="fcafterbuy_actions">
    <input type="hidden" name="fnc" value="">
    <input type="hidden" name="actshop" value="[{$oViewConf->getActiveShopId()}]">
    <input type="hidden" name="updatenav" value="">
    <input type="hidden" name="editlanguage" value="[{$editlanguage}]">
</form>

<form name="myedit" id="myedit" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid() }]
    <input type="hidden" name="cl" value="fcafterbuy_actions">
    <input type="hidden" name="fnc" value="">
</form>

<table>
    <tr>
        <th>
            [{oxmultilang ident="SHOP_MODULE_AFTERBUY_ACTION"}]
        </th>
    </tr>
    <tr>
        <td>
            <input
                    type="button"
                    name="transactionreset"
                    value="[{oxmultilang ident="SHOP_MODULE_AFTERBUY_RESET_TRANSACTIONDATA"}]"
                    onClick="Javascript:confirmReset()"
                    [{$readonly}]
            >
        </td>
    </tr>
</table>
<br/><br/><br/>
[{include file="bottomnaviitem.tpl"}]
[{include file="bottomitem.tpl"}]
