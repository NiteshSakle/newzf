<?php

namespace Album\Form;
use Zend\Form\Form;
use Zend\Form\Element;

class SearchForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('album');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('method', 'get');
	
        $search = new Element\Text('search');
        $search->setLabel('Search')
                ->setAttribute('class', 'required')
                ->setAttribute('placeholder', 'Search');
        
        $submit = new Element\Submit('submit');
        $submit->setValue('Search')
                ->setAttribute('class', 'btn btn-primary');
        $this->add($search);
	
        $this->add($submit);
    }
}