<?php

/**
 * Description of HtmlUtils
 *
 * @author mauro
 */
class HtmlUtils {
    /**
     *
     * @param <array> $remedies lista de Remedy
     * @return <type> um html com o select.
     */
    public static function makeSelectForRemedy($remedies) {
        $onselect = "$(this.form).find('#qt_pills').val($(this).find(':selected').attr('qty'));$(this.form).find('#qt_cx').val('');";
        //$onselect = "alert($(this).find(':selected').attr('qty'));";
        //$onselect = "alert('this :selected');";
        $first = '<option value="">Selecione o Medicamento</option>';
        $ret = sprintf(
                '<select name="remedy" id="remedy" onchange="%s"'
                    . 'class="formElementSelect formElementInteract chosen-select">%s',
                $onselect,
                $first
            );
        foreach ($remedies as $earemedy) {
            $ret .= sprintf(
                '<option value="%d" label="%s" qty="%d" descr="%s">%s (cx. c/ %d)</option>',
                $earemedy->id,
                $earemedy->name,
                $earemedy->qty,
                $earemedy->descr,
                $earemedy->name,
                $earemedy->qty
            );
        }
        $ret .= '</select>';
        return $ret;
    }

}
?>
