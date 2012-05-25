<?php

namespace PieCrust\Util;

use PieCrust\IPage;
use PieCrust\IPieCrust;
use PieCrust\PieCrustDefaults;
use PieCrust\Util\UriBuilder;


/**
 * Helper class for doing stuff with pages and posts.
 */
class PageHelper
{
    /**
     * Gets the relative path of a page.
     */
    public static function getRelativePath(IPage $page, $stripExtension = false)
    {
        return PathHelper::getRelativePagePath($page->getApp(), $page->getPath(), $page->getPageType(), $stripExtension);
    }

    /**
     * Gets a configuration value either on the given page, or on its parent
     * application.
     */
    public static function getConfigValue(IPage $page, $key, $appSection)
    {
        if ($page->getConfig()->hasValue($key))
            return $page->getConfig()->getValueUnchecked($key);
        return $page->getApp()->getConfig()->getValue($appSection.'/'.$key);
    }

    /**
     * Gets a configuration value either on the given page, or on its parent
     * application.
     */
    public static function getConfigValueUnchecked(IPage $page, $key, $appSection)
    {
        if ($page->getConfig()->hasValue($key))
            return $page->getConfig()->getValueUnchecked($key);
        return $page->getApp()->getConfig()->getValueUnchecked($appSection.'/'.$key);
    }
    
    /**
     * Gets a timestamp/date from a post info array.
     */
    public static function getPostDate(array $postInfo)
    {
        return mktime(0, 0, 0, intval($postInfo['month']), intval($postInfo['day']), intval($postInfo['year']));
    }
    
    /**
     * Gets whether the given page is a regular page.
     */
    public static function isRegular(IPage $page)
    {
        return $page->getPageType() == IPage::TYPE_REGULAR;
    }
    
    /**
     * Gets whether the given page is a blog post.
     */
    public static function isPost(IPage $page)
    {
        return $page->getPageType() == IPage::TYPE_POST;
    }
    
    /**
     * Gets whether the given page is a tag listing.
     */
    public static function isTag(IPage $page)
    {
        return $page->getPageType() == IPage::TYPE_TAG;
    }
    
    /**
     * Gets whether the given page is a category listing.
     */
    public static function isCategory(IPage $page)
    {
        return $page->getPageType() == IPage::TYPE_CATEGORY;
    }

    /**
     * Calls a function on every page found in the application.
     */
    public static function processPages(IPieCrust $pieCrust, $callback)
    {
        $pages = self::getPages($pieCrust);
        foreach ($pages as $page)
        {
            if (call_user_func($callback, $page) === false)
                break;
        }
    }

    /**
     * Gets all the pages found for in a website.
     */
    public static function getPages(IPieCrust $pieCrust)
    {
        return $pieCrust->getEnvironment()->getPages();
    }

    /**
     * Calls a function on every post found in the given blog.
     */
    public static function processPosts(IPieCrust $pieCrust, $blogKey, $callback)
    {
        $posts = self::getPosts($pieCrust, $blogKey);
        foreach ($posts as $post)
        {
            if (call_user_func($callback, $post) === false)
                break;
        }
    }

    /**
     * Gets all the posts found for in a website for a particular blog.
     */
    public static function getPosts(IPieCrust $pieCrust, $blogKey)
    {
        return $pieCrust->getEnvironment()->getPosts($blogKey);
    }

    /**
     * Gets a tag listing page.
     */
    public static function getTagPage(IPieCrust $pieCrust, $tag, $blogKey = null)
    {
        if ($blogKey == null)
        {
            $blogKeys = $pieCrust->getConfig()->getValueUnchecked('site/blogs');
            $blogKey = $blogKeys[0];
        }
        $pathPrefix = '';
        if ($blogKey != PieCrustDefaults::DEFAULT_BLOG_KEY)
            $pathPrefix = $blogKey . DIRECTORY_SEPARATOR;

        $pageRepository = $pieCrust->getEnvironment()->getPageRepository();

        $uri = UriBuilder::buildTagUri($pieCrust->getConfig()->getValue($blogKey.'/tag_url'), $tag);
        $path = $pieCrust->getPagesDir() . $pathPrefix . PieCrustDefaults::TAG_PAGE_NAME . '.html';
        if (!is_file($path))
            return null;

        $page = $pageRepository->getOrCreatePage(
            $uri,
            $tagPagePath,
            IPage::TYPE_TAG,
            $blogKey,
            $tag
        );
        return $page;
    }
}