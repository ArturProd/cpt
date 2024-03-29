<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\BlogBundle\Tests\Model;

use Cpt\BlogBundle\Permalink\CategoryPermalink;
use Cpt\BlogBundle\Model\CategoryInterface;
use Cpt\BlogBundle\Model\PostInterface;

class CategoryPermalinkTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateWithCategory()
    {
        $category = $this->getMock('Cpt\BlogBundle\Model\CategoryInterface');
        $category->expects($this->any())->method('getSlug')->will($this->returnValue('the-category'));

        $post = $this->getMock('Cpt\BlogBundle\Model\PostInterface');
        $post->expects($this->any())->method('getCategory')->will($this->returnValue($category));
        $post->expects($this->any())->method('getSlug')->will($this->returnValue('the-slug'));

        $permalink = new CategoryPermalink();
        $this->assertEquals('the-category/the-slug', $permalink->generate($post));
    }

    public function testGenerateWithoutCategory()
    {
        $post = $this->getMock('Cpt\BlogBundle\Model\PostInterface');
        $post->expects($this->any())->method('getCategory')->will($this->returnValue(null));
        $post->expects($this->any())->method('getSlug')->will($this->returnValue('the-slug'));

        $permalink = new CategoryPermalink();
        $this->assertEquals('the-slug', $permalink->generate($post));
    }

    public function testGetParametersWithCategory()
    {
        $permalink = new CategoryPermalink();
        $expected = array(
            'category' => 'the-category',
            'slug' => 'the-slug',
        );

        $this->assertEquals($expected, $permalink->getParameters('the-category/the-slug'));
    }

    public function testGetParametersWithoutCategory()
    {
        $permalink = new CategoryPermalink();
        $expected = array(
            'category' => null,
            'slug' => 'the-slug',
        );

        $this->assertEquals($expected, $permalink->getParameters('the-slug'));
    }

    public function testGetParametersWithoutCategoryAndExtra()
    {
        $this->setExpectedException('InvalidArgumentException');

        $permalink = new CategoryPermalink();
        $expected = array(
            'category' => 'the-category',
            'slug' => 'the-slug',
        );

        $this->assertEquals($expected, $permalink->getParameters('the-category/the-slug/asdsaasds'));
    }
}
