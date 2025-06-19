<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SubscriberHelper.php,v 1.1.2.8 2023/11/14 16:08:24 rtigero Exp $

namespace Pmb\DSI\Helper;

use Pmb\Common\Helper\Helper;
use Pmb\DSI\Helper\DsiDocument;
use Pmb\DSI\Models\Diffusion;
use Pmb\DSI\Models\SubscriberList\Subscribers\Subscriber;

class SubscriberHelper
{
    public const PREFIX_PATTERN = "subscriber_";

    public const PREFIX_H2O = "subscriber.";

    public const PATTERN = [
        "!!subscriber_name!!",
        "!!subscriber_sexe!!",
        "!!subscriber_mail!!",
        "!!subscriber_phone!!",
        "!!subscriber_login!!",
        "!!subscriber_auth_code!!",
        "!!subscriber_date_auth_code!!",
        "!!subscriber_auto_connection_link!!",
        "!!subscriber_unsubscribe_link!!"
    ];

    public const HTTP_QUERY_AUTO_CONNEXION = [
        "code=!!subscriber_auth_code!!",
        "emprlogin=!!subscriber_login!!",
        "date_conex=!!subscriber_date_auth_code!!"
    ];

    /**
     * Permet de remplacer les motifs dans les templates
     *
     * @param string $template
     * @param Subscriber $subscriber
     * @return string $template
     */
    public static function replacePattern(string $template, Subscriber $subscriber, Diffusion $diffusion)
    {
        global $opac_connexion_phrase, $opac_url_base;
        global $msg, $dsi_connexion_auto;

        $replace = [
            'name' => '',
            'civilite' => '',
            'mail' => '',
            'phone' => '',
            'login' => '',
            'auth_code' => '',
            'date_auth_code' => '',
            'auto_connection_link' => '',
            'unsubscribe_link' => ''
        ];

        if (!empty($subscriber->getIdEmpr())) {
			$empr = new \emprunteur($subscriber->getIdEmpr());
            switch ($empr->emprSexe ?? 0) {
                case "2":
                    $emprCivilite = $msg["civilite_madame"];
                    break;
                case "1":
                    $emprCivilite = $msg["civilite_monsieur"];
                    break;
                default:
                    $emprCivilite = $msg["civilite_unknown"];
                    break;
            }


            $date = time();
            $authCode = md5($opac_connexion_phrase . $empr->login . $date);

			$replace['name'] = \emprunteur::get_name($subscriber->getIdEmpr(), 1);
			$replace['civilite'] = $emprCivilite;
			$replace['mail'] = $empr->mail;
			$replace['phone'] = $empr->tel1;
			$replace['login'] = $empr->login;
			$replace['auth_code'] = $authCode;
			$replace['date_auth_code'] = $date;
            $replace['unsubscribe_link'] = "<a href='".$opac_url_base."index.php?lvl=dsi&action=unsubscribe&id_diffusion=".$diffusion->id."&code=".md5($opac_connexion_phrase . $subscriber->getIdEmpr() . $date)."&emprlogin=".$subscriber->getIdEmpr()."&date_conex=".$date."&empr_type=pmb'>".$msg["bannette_tpl_unsubscribe"]."</a>";
            if ($dsi_connexion_auto) {
                $replace['auto_connection_link'] = "
                <a href='{$opac_url_base}empr.php?code={$authCode}&emprlogin={$subscriber->getIdEmpr()}&date_conex={$date}'>
                {$msg["selvars_empr_auth_opac"]}
                </a>
                ";
            }
		} else {
            $date = time();
            $authCode = md5($opac_connexion_phrase . $subscriber->getIdSubscriber() . $date);

			$replace['name'] = $subscriber->getName();
			$replace['civilite'] = $msg["civilite_unknown"];
            $replace['unsubscribe_link'] = "<a href='".$opac_url_base."index.php?lvl=dsi&action=unsubscribe&id_diffusion=".$diffusion->id."&code=".$authCode."&emprlogin=".$subscriber->getIdSubscriber()."&date_conex=".$date."&empr_type=other'>".$msg["bannette_tpl_unsubscribe"]."</a>";
            $replace = array_merge($replace, Helper::toArray($subscriber->settings));
		}

        return str_replace(static::PATTERN, $replace, $template);
    }

    public static function format(string $template, Subscriber $subscriber, bool $stripTags = false, Diffusion $diffusion)
    {
        $template = static::parseDom($template, $stripTags);
        $template = static::replacePattern($template, $subscriber, $diffusion);
        return $stripTags ? strip_tags($template) : $template;
    }

    public static function parseDom(string $template, bool $stripTags = false)
    {
        $dsiDocument = new DsiDocument();
        $dsiDocument->loadHTML($template);
        $dsiDocument->formatHTML();
        return $stripTags ? trim($dsiDocument->textContent) : $dsiDocument->saveHTML();
    }

    public static function getPatternList()
    {
        global $msg;

        $patternList = [];
        foreach (static::PATTERN as $pattern) {
            $label = trim($pattern, "!");
            $label = $msg["dsi_{$label}"] ?? $label;
            $patternList[$pattern] = $label;
        }
        return $patternList;
    }

    public static function getH2oPatternList()
    {
        global $msg;

        $patternList = [];
        foreach (static::getPatternList() as $pattern => $label) {
            $pattern = trim($pattern, "!");
            $pattern = str_replace(
                static::PREFIX_PATTERN,
                static::PREFIX_H2O,
                $pattern
            );
            $patternList[$pattern] = $label;
        }
        return $patternList;
    }

    public static function getTree()
    {
        global $msg;

        $children = [];
        foreach (static::getH2oPatternList() as $pattern => $label) {
            $children[] = [
                'var' => $pattern,
                'desc' => $label,
            ];
        }

        return [
            [
                'var' => "subscriber",
                'desc' => $msg['tree_subscriber_desc'] ?? "subscriber",
                'children' => $children
            ]
        ];
    }

    public static function h2oLookup($name, $h2oContext)
    {
        $prefixName = ":" . static::PREFIX_H2O;
        if (strpos($name, $prefixName) === 0) {
            $pattern = str_replace($prefixName, "", $name);
            $pattern = "!!" . static::PREFIX_PATTERN . $pattern . "!!";

            if (in_array($pattern, static::PATTERN)) {
                return $pattern;
            }
            return "";
        }
        return null;
    }

    public static function get_empr_status()
    {
        $ac = new \acces();
		$dom = $ac->setDomain(2);
		$t_u = array();
		$t_u[] = [
			"value" => 0,
			"label" => $dom->getComment('user_prf_def_lib')
		];
		$qu = $dom->loadUsedUserProfiles();
		$ru = pmb_mysql_query($qu);
		if (pmb_mysql_num_rows($ru)) {
			while (($row = pmb_mysql_fetch_object($ru))) {
				$t_u[] = [
					"value" => $row->prf_id,
					"label" => $row->prf_name
				];
			}
		}
		return $t_u;
    }

    public static function get_empr_categ()
    {
        $result = array();

        $requete = "SELECT id_categ_empr, libelle FROM empr_categ ORDER BY libelle ";
	    $res = pmb_mysql_query($requete);

        if(pmb_mysql_num_rows($res)) {
            while($row = pmb_mysql_fetch_assoc($res)) {
                $result[] = [
                    "value" => $row["id_categ_empr"],
                    "label" => $row["libelle"]
                ];
            }
        }

        return $result;
    }

    public static function get_empr_groups()
    {
        $result = array();

        $requete = "SELECT id_groupe, libelle_groupe FROM groupe ORDER BY libelle_groupe";
        $res = pmb_mysql_query($requete);

        if(pmb_mysql_num_rows($res)) {
            while($row = pmb_mysql_fetch_assoc($res)) {
                $result[] = [
                    "value" => $row["id_groupe"],
                    "label" => $row["libelle_groupe"]
                ];
            }
        }

        return $result;
    }
}
