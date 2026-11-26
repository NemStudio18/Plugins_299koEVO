<?php

namespace Blog\Entities;

use Utils\Util;
use Core\Router\Router;
use Content\ContentParser;

defined('ROOT') or exit('Access denied!');

class News
{
    private $id;
    private $name;
    private $date;
    private $created_at = null;
    private $published_at = null;
    private $updated_at = null;
    private $content = '';
    private $intro;
    private $seoDesc;
    private $draft;
    private $img;
    private $commentsOff;
    private array $allowedGroups = [];

    private ContentParser $parser;

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
            $this->created_at = $val['created_at'] ?? ($val['date'] ?? null);
            $this->published_at = $val['published_at'] ?? ($val['date'] ?? null);
            $this->updated_at = $val['updated_at'] ?? null;
            $this->date = $this->published_at ?? ($val['date'] ?? null);
            $this->draft = $val['draft'] ?? '1';
            $this->img = (isset($val['img']) ? $val['img'] : '');
            $this->commentsOff = (isset($val['commentsOff']) ? $val['commentsOff'] : 0);
            $this->categories = $val['categories'] ?? [];
            $this->allowedGroups = is_array($val['allowedGroups'] ?? null) ? $val['allowedGroups'] : [];
        }
        $this->parser = new ContentParser($this->content);
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
        $this->setPublishedAt($val);
    }

    public function setPublishedAt($val)
    {
        if ($val === null || empty($val)) {
            $val = date('Y-m-d H:i:s');
        }
        $val = trim(str_replace('T', ' ', (string)$val));
        if (strlen($val) === 16) {
            $val .= ':00';
        }
        $this->published_at = $val;
        $this->date = $val;
    }

    public function getPublishedAt(): ?string
    {
        return $this->published_at ?? $this->date;
    }

    public function setCreatedAt($val)
    {
        if ($val === null || empty($val)) {
            $val = date('Y-m-d H:i:s');
        }
        $val = trim(str_replace('T', ' ', (string)$val));
        if (strlen($val) === 16) {
            $val .= ':00';
        }
        $this->created_at = $val;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at ?? ($this->published_at ?? $this->date);
    }

    public function isScheduled(): bool
    {
        $pub = $this->getPublishedAt();
        if (empty($pub)) {
            return false;
        }
        $ts = strtotime($pub);
        return ($ts !== false && $ts > time());
    }

    public function isDraft(): bool
    {
        return ((string)$this->draft === "1" || (int)$this->draft === 1);
    }

    public function isPublic(): bool
    {
        return !$this->isDraft() && !$this->isScheduled();
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

    public function getContentWithoutShortcodes(): string
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
        return self::formatReadableDate($this->getPublishedAt());
    }

    public function getReadablePublishedDate(): string
    {
        return self::formatReadableDate($this->getPublishedAt());
    }

    public function getReadableCreatedDate(): string
    {
        return self::formatReadableDate($this->getCreatedAt());
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

    public function getAllowedGroups(): array
    {
        return $this->allowedGroups ?? [];
    }

    public function setAllowedGroups(array $groups): void
    {
        $this->allowedGroups = array_values(array_filter(array_map('trim', $groups)));
    }

    public function isRestricted(): bool
    {
        return !empty($this->allowedGroups);
    }
}
