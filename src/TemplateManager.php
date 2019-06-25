<?php

class TemplateManager
{
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
        $_user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $APPLICATION_CONTEXT->getCurrentUser();
        if($_user) {
            (strpos($text, '[user:first_name]') !== false) and $text = str_replace('[user:first_name]'       , ucfirst(mb_strtolower($_user->firstname)), $text);
        }

        return $text;
    }



    private function replaceText($text, Quote $quote){

//        $quote = $data['quote'];

        //$quoteFrom = QuoteRepository::getInstance()->getById($quote->id);
        $site = SiteRepository::getInstance()->getById($quote->siteId);
        $destination = DestinationRepository::getInstance()->getById($quote->destinationId);

       /* if(strpos($text, '[quote:destination_link]') !== false){
            $destination = DestinationRepository::getInstance()->getById($quote->destinationId);
        } //why?*/

        $containsSummaryHtml = strpos($text, '[quote:summary_html]');
        $containsSummary     = strpos($text, '[quote:summary]');

        if ($containsSummaryHtml !== false) {
            $text = str_replace(
                '[quote:summary_html]',
                Quote::renderHtml($quoteFrom),
                $text
            );
        }
        if ($containsSummary !== false) {
            $text = str_replace(
                '[quote:summary]',
                Quote::renderText($quoteFrom),
                $text
            );
        }


        if(strpos($text, '[quote:destination_name]') !== false){
            $text = str_replace('[quote:destination_name]',$destination->countryName,$text);
        }

        if (isset($destination))
            $text = str_replace('[quote:destination_link]', $site->url . '/' . $destination->countryName . '/quote/' . $quote->id, $text);
        else
            $text = str_replace('[quote:destination_link]', '', $text);

        return $text;
    
    }
}
