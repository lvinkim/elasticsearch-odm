<?php
/**
 * Created by PhpStorm.
 * User: lvinkim
 * Date: 2018/8/7
 * Time: 8:59 PM
 */

namespace Lvinkim\ElasticSearchODM;


use Lvinkim\ElasticSearchODM\Annotations\AbstractField;
use Lvinkim\ElasticSearchODM\Annotations\Field;

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

    public function getId($entity)
    {
        if (!$this->annotationParser->isEntityClass($entity)) {
            return $entity->id ?? null;
        }

        try {
            $reflectClass = new \ReflectionClass($entity);
        } catch (\Exception $exception) {
            return null;
        }

        foreach ($reflectClass->getProperties() as $entityField) {
            if ($entityField->isStatic()) {
                continue;
            }
            $annotation = $this->annotationParser->getPropertyAnnotation($entityField);
            if (null === $annotation || !$annotation->id) {
                continue;
            }
            $entityField->setAccessible(true);
            return $entityField->getValue($entity);
        }
        return null;
    }

    /**
     * @param $entity
     * @param $documentId
     * @return bool
     */
    public function setId($entity, $documentId)
    {
        if (!$this->annotationParser->isEntityClass($entity)) {
            return false;
        }
        try {
            $reflectClass = new \ReflectionClass($entity);
        } catch (\Exception $exception) {
            return false;
        }
        foreach ($reflectClass->getProperties() as $entityField) {
            if ($entityField->isStatic()) {
                continue;
            }
            $annotation = $this->annotationParser->getPropertyAnnotation($entityField);
            if (null === $annotation || !$annotation->id) {
                continue;
            }
            $entityField->setAccessible(true);
            $entityField->setValue($entity, $documentId);
            return true;
        }
        return false;
    }

    public function entityToDocument($entity, string $entityClassName)
    {
        if (!$this->annotationParser->isEntityClass($entityClassName)) {
            return $entity;
        }

        $entityProperties = $this->getEntityProperties($entityClassName);

        $document = [];
        /**
         * @var \ReflectionProperty $entityField
         * @var AbstractField $annotation
         */
        foreach ($entityProperties as $entityField => $annotation) {

            if (!($annotation instanceof AbstractField)) {
                continue;
            }

            $entityField->setAccessible(true);

            $entityFieldValue = $entityField->getValue($entity);
            $documentPropertyValue = $this->entityFieldToDocumentProperty($entityFieldValue, $annotation);

            $fieldName = $annotation->name ?? $entityField->getName();
            if ($annotation->id) {
                $document['_id'] = $documentPropertyValue;
            }

            $document['_source'][$fieldName] = $documentPropertyValue;
        }
        return $document;
    }

    /**
     * 由 Entity 的 EmbedOne 属性的对象转换回 ES Document 字段
     * @param $entityFieldValue
     * @param AbstractField $annotation
     * @return array|bool|float|int|string
     */
    public function entityFieldToDocumentProperty($entityFieldValue, AbstractField $annotation)
    {
        $fieldType = $annotation->type;

        if (!$fieldType) {
            $fieldType = Field::$defaultPropertyTypeMap[$annotation->property] ?? '';
        }

        switch ($fieldType) {
            case 'string':
                $documentPropertyValue = strval($entityFieldValue);
                break;
            case 'bool':
                $documentPropertyValue = boolval($entityFieldValue);
                break;
            case 'int':
                $documentPropertyValue = intval($entityFieldValue);
                break;
            case 'float':
                $documentPropertyValue = floatval($entityFieldValue);
                break;
            case 'array':
                $documentPropertyValue = is_array($entityFieldValue) ? $entityFieldValue : (array)($entityFieldValue);
                break;
            case 'date':
                if ($entityFieldValue instanceof \DateTime) {
                    $documentPropertyValue = $entityFieldValue->getTimestamp() * 1000;
                } else {
                    $documentPropertyValue = $entityFieldValue;
                }
                break;
            case 'embedOne':
                $documentPropertyValue = $this->entityEmbedOneToDocumentProperty($entityFieldValue, $annotation);
                break;
            case 'embedMany':
                $documentPropertyValue = $this->entityEmbedManyToDocumentProperty((array)$entityFieldValue, $annotation);
                break;
            case 'raw':
            default:
                $documentPropertyValue = $entityFieldValue;
                break;
        }

        return $documentPropertyValue;
    }

    /**
     * 由 Entity 的 EmbedMany 属性的对象转换回 ES Document 字段
     * @param $embed
     * @param AbstractField $annotation
     * @return array
     */
    public function entityEmbedOneToDocumentProperty($embed, AbstractField $annotation)
    {
        $className = $annotation->target;

        try {
            $reflectClass = new \ReflectionClass($className);
        } catch (\Exception $exception) {
            return $embed;
        }

        $property = [];

        foreach ($reflectClass->getProperties() as $field) {
            if ($field->isStatic()) {
                continue;
            }

            $annotation = $this->annotationParser->getPropertyAnnotation($field);

            $propertyName = $annotation->name ?? $field->getName();

            try {
                $reflectProperty = new \ReflectionProperty($embed, $propertyName);

                $reflectProperty->setAccessible(true);
                $fieldValue = $reflectProperty->getValue($embed);

                $propertyValue = $this->entityFieldToDocumentProperty($fieldValue, $annotation);

            } catch (\Exception $exception) {
                $propertyValue = null;
            }

            $property[$propertyName] = $propertyValue;
        }

        return $property;
    }

    public function entityEmbedManyToDocumentProperty(array $embeds, AbstractField $annotation)
    {
        $documents = [];
        foreach ($embeds as $embed) {
            $documents[] = $this->entityEmbedOneToDocumentProperty($embed, $annotation);
        }
        return $documents;
    }

    /**
     * @param $document
     * @param $entityClassName
     * @return mixed
     */
    public function documentToEntity($document, $entityClassName)
    {
        if (!$this->annotationParser->isEntityClass($entityClassName)) {
            return $document;
        }
        $entityProperties = $this->getEntityProperties($entityClassName);

        $entity = new $entityClassName();

        /**
         * @var \ReflectionProperty $entityField
         * @var AbstractField $annotation
         */
        foreach ($entityProperties as $entityField => $annotation) {

            if (!($annotation instanceof AbstractField)) {
                continue;
            }

            $fieldName = $annotation->name ?? $entityField->getName();

            if ($annotation->id) {
                $documentValue = $document['_id'] ?? null;
            } else {
                $documentValue = $document['_source'][$fieldName] ?? null;
            }

            $entityFieldValue = $this->documentPropertyToEntityField($documentValue, $annotation);

            $entityField->setAccessible(true);
            $entityField->setValue($entity, $entityFieldValue);
        }

        return $entity;
    }

    public function documentPropertyToEntityField($documentValue, AbstractField $annotation)
    {
        $fieldType = $annotation->type;

        if (!$fieldType) {
            $fieldType = Field::$defaultPropertyTypeMap[$annotation->property] ?? '';
        }

        switch ($fieldType) {
            case 'string':
                $entityFieldValue = strval($documentValue);
                break;
            case 'bool':
                $entityFieldValue = boolval($documentValue);
                break;
            case 'int':
                $entityFieldValue = intval($documentValue);
                break;
            case 'float':
                $entityFieldValue = floatval($documentValue);
                break;
            case 'array':
                $entityFieldValue = is_array($documentValue) ? $documentValue : (array)($documentValue);
                break;
            case 'date':
                if (is_int($documentValue)) {
                    $entityFieldValue = (new \DateTime())->setTimestamp(intval($documentValue / 1000));
                } elseif (is_string($documentValue)) {
                    $entityFieldValue = (new \DateTime($documentValue));
                } else {
                    $entityFieldValue = $documentValue;
                }
                break;
            case 'embedOne':
                $entityFieldValue = $this->documentPropertyToEntityEmbedOne($documentValue, $annotation);
                break;
            case 'embedMany':
                $entityFieldValue = $this->documentPropertyToEntityEmbedMany((array)$documentValue, $annotation);
                break;
            case 'raw':
            default:
                $entityFieldValue = $documentValue;
                break;
        }

        return $entityFieldValue;
    }


    /**
     * 由 ES Document 字段设置成对应的 Entity EmbedOne 对象
     * @param $documentProperty
     * @param AbstractField $annotation
     * @return mixed
     */
    public function documentPropertyToEntityEmbedOne($documentProperty, AbstractField $annotation)
    {
        $className = $annotation->target;

        try {
            $reflectClass = new \ReflectionClass($className);
        } catch (\Exception $exception) {
            return $documentProperty;
        }

        $embed = new $className;

        foreach ($reflectClass->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }
            /** @var AbstractField $annotation */
            $annotation = $this->annotationParser->getPropertyAnnotation($property);

            $propertyName = $annotation->name ?? $property->getName();

            $propertyValue = $documentProperty[$propertyName] ?? null;
            $propertyValue = $this->documentPropertyToEntityField($propertyValue, $annotation);

            $property->setAccessible(true);
            $property->setValue($embed, $propertyValue);
        }

        return $embed;
    }

    /**
     * 由 ES Document 字段设置成对应的 Entity EmbedMany 对象
     * @param array $documentProperties
     * @param AbstractField $annotation
     * @return array
     */
    public function documentPropertyToEntityEmbedMany(array $documentProperties, AbstractField $annotation)
    {
        $embeds = [];
        foreach ($documentProperties as $documentProperty) {
            $embeds[] = $this->documentPropertyToEntityEmbedOne($documentProperty, $annotation);
        }

        return $embeds;
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