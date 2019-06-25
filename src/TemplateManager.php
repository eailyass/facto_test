<?php

/*
Le but est de rendre la classe plus lisible, pour cela il faut pensr à réamenagerla fonction computeText qui se montre un peu chargée en la divisant à plusieurs petites fonctions puis ajouter des commentaires pour rendre le tout plus lisible
 */


class TemplateManager
{
    // ne pas changer la signature de cette fonction
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    // tenter de réduire la taille de cette fonction, elle doit servir à remplacer les valeurs mises entre quotes dans le texte en entrée par des valeurs de nos entités 

    private function computeText($text, array $data)
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;

        if ($quote)
        $text= $this->replaceText($text, $quote);

        

        /*
         * USER
         * [user:*]
         */
        $user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $APPLICATION_CONTEXT->getCurrentUser();
        if($user) 
            $text= $this->replaceUser($text,$user);

        return $text;
    }


    // fonction servant à remplacer les valeurs de l'entité quote dans le texte

    private function replaceText($text, Quote $quote)
    {

        $site = SiteRepository::getInstance()->getById($quote->siteId);
        $destination = DestinationRepository::getInstance()->getById($quote->destinationId);


        if (strpos($text, '[quote:summary_html]') !== false) {
            $text = str_replace(
                '[quote:summary_html]',
                Quote::renderHtml($quoteFrom),
                $text
            );
        }
        if (strpos($text, '[quote:summary]') !== false) {
            $text = str_replace(
                '[quote:summary]',
                Quote::renderText($quoteFrom),
                $text
            );
        }

        if(strpos($text, '[quote:destination_name]') !== false){
            $text = str_replace(
                '[quote:destination_name]',
                $destination->countryName,
                $text);
        }

        if (isset($destination))
            $text = str_replace('[quote:destination_link]',
            $site->url . '/' . $destination->countryName . '/quote/' . $quote->id,
            $text);
        else
            $text = str_replace('[quote:destination_link]', '', $text);

        return $text;
    
    }

    // fonction servant à remplacer les valeurs de l'entité User dans le texte

    private function replaceUser($text, User $user)
    {
        if(strpos($text, '[user:first_name]') !== false)
         $text = str_replace('[user:first_name]',
         ucfirst(mb_strtolower($user->firstname)),
         $text);
        return $text;
    }
}
