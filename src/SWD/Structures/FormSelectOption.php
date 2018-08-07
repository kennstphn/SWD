<?php
namespace SWD\Structures;


class FormSelectOption
{
    protected 
        $selected, 
        $disabled, 
        $value, 
        $optionGroup, 
        $text;

    /**
     * @return boolean
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param boolean $disabled
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
    }


    /**
     * @return boolean
     */
    public function getSelected()
    {
        return $this->selected;
    }

    /**
     * @param boolean $selected
     */
    public function setSelected($selected)
    {
        $this->selected = $selected;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getOptionGroup()
    {
        return $this->optionGroup;
    }

    /**
     * @param string $optionGroup
     */
    public function setOptionGroup($optionGroup)
    {
        $this->optionGroup = $optionGroup;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    function __toString()
    {
        $selected = ($this->getSelected())? 'selected':'';
        $disabled = ($this->getDisabled())? 'disabled':'';
        $text = ( ! is_null($this->getText()) && $this->getText() !== '')? $this->getText() : $this->getValue();
        return "<option value='{$this->getValue()}' {$selected} {$disabled}>{$text}</option>".PHP_EOL;
    }

}