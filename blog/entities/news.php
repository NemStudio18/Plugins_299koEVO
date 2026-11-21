<?php

use Utils\Util;
use Core\Router\Router;

/**
 * @copyright (C) 2022, 299Ko, based on code (2010-2021) 99ko https://github.com/99kocms/
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 * @author Jonathan Coulet <j.coulet@gmail.com>
 * @author Maxence Cauderlier <mx.koder@gmail.com>
 * @author Frédéric Kaplon <frederic.kaplon@me.com>
 * @author Florent Fortat <florent.fortat@maxgun.fr>
 *
 * @package 299Ko https://github.com/299Ko/299ko
 */
defined('ROOT') or exit('Access denied!');

class news
{

    private $id;
    private $name;
    private $date;
    private $updated_at = null;
    private $content = '';
    private $intro;
    private $seoDesc;
    private $draft;
    private $img;
    private $commentsOff;

    private \Content\ContentParser $parser;

    /**
     * Array with Id of categories
     * @var array
     */
    public array $categories = [];

    public function __construct($val = [])
    {
        if (count($val) > 0) {
            $this->id = $val['id'] ?? null;
            $this->name = $val['name'] ?? '';
            $this->content = (string)($val['content'] ?? '');
            $this->intro = $val['intro'] ?? '';
            $this->seoDesc = $val['seoDesc'] ?? '';
            $this->date = $val['date'] ?? null;
            $this->updated_at = $val['updated_at'] ?? null;
            $this->draft = $val['draft'] ?? '1';
            $this->img = (isset($val['img']) ? $val['img'] : '');
            $this->commentsOff = (isset($val['commentsOff']) ? $val['commentsOff'] : 0);
            $this->categories = $val['categories'] ?? [];
        }
        $this->parser = new \Content\ContentParser($this->content);
    }

    public function setId($val)
    {
        $this->id = intval($val);
    }

    public function setName($val)
    {
        $this->name = trim($val);
    }

    public function setContent($val)
    {
        $this->content = trim($val);
        $this->parser->setContent($this->content);
    }

    public function setIntro($val)
    {
        $this->intro = trim($val);
    }

    public function setSEODesc($val)
    {
        $this->seoDesc = trim($val);
    }

    public function setDate($val)
    {
        if ($val === null || empty($val)) {
            $val = date('Y-m-d H:i:s');
        }
        $val = trim(str_replace('T', ' ', (string)$val));
        if (strlen($val) === 16) {
            $val .= ':00';
        }
        $this->date = $val;
    }

    public function setUpdatedAt($val)
    {
        if ($val !== null && !empty($val)) {
            $val = trim(str_replace('T', ' ', (string)$val));
            if (strlen($val) === 16) {
                $val .= ':00';
            }
            $this->updated_at = $val;
        } else {
            $this->updated_at = null;
        }
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    public function getHtmlDatetime(): string
    {
        if (empty($this->date)) {
            return date('Y-m-d\TH:i');
        }
        $ts = strtotime($this->date);
        return $ts ? date('Y-m-d\TH:i', $ts) : date('Y-m-d\TH:i');
    }

    public function setDraft($val)
    {
        $this->draft = trim($val);
    }

    public function setImg($val)
    {
        $this->img = trim($val);
    }

    public function setCommentsOff($val)
    {
        $this->commentsOff = trim($val);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getParsedContent(): string
    {
        return $this->parser->getParsedContent();
    }

    public function getContentWithoutShortcodes():string
    {
        return $this->parser->getWithoutShortcodesContent();
    }

    public function getUrl()
    {
        return Router::getInstance()->generate('blog-read', ['name' => Util::strToUrl($this->name), 'id' => $this->id]);
    }

    public function getIntro()
    {
        return ($this->intro === '' ? false : $this->intro);
    }

    public function getSEODesc()
    {
        return ($this->seoDesc === '' ? false : $this->seoDesc);
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getReadableDate(): string
    {
        return self::formatReadableDate($this->date);
    }

    public function getReadableUpdatedDate(): ?string
    {
        if (empty($this->updated_at)) {
            return null;
        }
        return self::formatReadableDate($this->updated_at);
    }

    public static function formatReadableDate($dateStr): string
    {
        if (empty($dateStr)) {
            return '';
        }
        try {
            $dt = new \DateTime($dateStr);
            $hasTime = (strlen(trim((string)$dateStr)) > 10 && $dt->format('H:i:s') !== '00:00:00');
            if (class_exists('IntlDateFormatter')) {
                $locale = class_exists('\\Core\\Lang') ? (\Core\Lang::get('locale') ?: 'fr_FR') : 'fr_FR';
                $fmt = new \IntlDateFormatter(
                    $locale,
                    \IntlDateFormatter::LONG,
                    $hasTime ? \IntlDateFormatter::SHORT : \IntlDateFormatter::NONE
                );
                return $fmt->format($dt);
            }
            return $hasTime ? $dt->format('d/m/Y à H:i') : $dt->format('d/m/Y');
        } catch (\Exception $e) {
            return (string)$dateStr;
        }
    }

    public function getDraft()
    {
        return $this->draft;
    }

    public function getImg()
    {
        return $this->img;
    }

    public function getImgUrl()
    {
        $rawImg = trim((string)($this->img ?? ''));
        if ($rawImg === '') {
            return '';
        }
        if (preg_match('~^https?://~i', $rawImg)) {
            return $rawImg;
        }
        $normalized = ltrim(str_replace('\\', '/', $rawImg), '/');
        if (str_starts_with($normalized, 'data/')) {
            return Util::urlBuild($normalized);
        }
        if (file_exists(UPLOAD . 'blog/' . $normalized)) {
            return Util::urlBuild(UPLOAD . 'blog/' . $normalized);
        }
        if (file_exists(UPLOAD . 'galerie/' . $normalized)) {
            return Util::urlBuild(UPLOAD . 'galerie/' . $normalized);
        }
        return Util::urlBuild($normalized);
    }

    public function getCommentsOff()
    {
        return $this->commentsOff;
    }

}