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
        {
            $quoteFrom = QuoteRepository::getInstance()->getById($quote->id);
            $site = SiteRepository::getInstance()->getById($quote->siteId);
            $quoteDestination = quoteDestinationRepository::getInstance()->getById($quote->quoteDestinationId);

            if(strpos($text, '[quote:quoteDestination_link]') !== false){
                $quoteDestination = quoteDestinationRepository::getInstance()->getById($quote->quoteDestinationId);
            }

            $containsSummaryHtml = strpos($text, '[quote:summary_html]');
            $containsSummary     = strpos($text, '[quote:summary]');

            if ($containsSummaryHtml !== false || $containsSummary !== false) {
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
            }

            (strpos($text, '[quote:quoteDestination_name]') !== false) and $text = str_replace('[quote:quoteDestination_name]',$quoteDestination->countryName,$text);
        }

        if (isset($quoteDestination))
            $text = str_replace('[quote:quoteDestination_link]', $site->url . '/' . $quoteDestination->countryName . '/quote/' . $quoteFrom->id, $text);
        else
            $text = str_replace('[quote:quoteDestination_link]', '', $text);

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
}
