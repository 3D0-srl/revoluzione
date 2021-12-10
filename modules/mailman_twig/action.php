<?php
use FormBuilder\FormBuilder;
use Mailman\MailmanFormBuilderElement;
use Mailman\MailmanFormBuilderAction;


FormBuilder::registerAction(MailmanFormBuilderAction::class);
FormBuilder::registerElement(MailmanFormBuilderElement::class);