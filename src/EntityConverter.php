<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 2018/8/7
 * Time: 8:59 PM
 */

namespace Lvinkim\ElasticSearchODM;


use Lvinkim\ElasticSearchODM\Annotations\AbstractField;

class EntityConverter
{
    /** @var AnnotationParser */
    private $annotationParser;

    /**
     * EntityConverter constructor.
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct()
    {
        $this->annotationParser = new AnnotationParser();
    }

    /**
     * @param $entityClassName
     * @return array|bool
     */
    public function getProperties($entityClassName)
    {
        $entityProperties = $this->getEntityProperties($entityClassName);

        $properties = [];
        /**
         * @var \ReflectionProperty $entityField
         * @var AbstractField $annotation
         */
        foreach ($entityProperties as $entityField => $annotation) {

            $fieldName = $annotation->name ?? $entityField->getName();

            $property = ['type' => $annotation->property];
            $property = array_merge($property, $annotation->options);

            $properties[$fieldName] = $property;
        }

        return $properties;
    }

    /**
     * @param $entityClassName
     * @return bool|\Generator
     */
    private function getEntityProperties($entityClassName)
    {
        if (!$this->annotationParser->isEntityClass($entityClassName)) {
            return false;
        }

        try {
            $reflectClass = new \ReflectionClass($entityClassName);
        } catch (\Exception $exception) {
            return false;
        }

        foreach ($reflectClass->getProperties() as $entityField) {
            if ($entityField->isStatic()) {
                continue;
            }
            $annotation = $this->annotationParser->getPropertyAnnotation($entityField);

            yield $entityField => $annotation;
        }

        return true;
    }
}