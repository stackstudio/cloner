<?php
namespace verbb\cloner\controllers;

use verbb\cloner\Cloner;

use Craft;
use craft\element\Entry;
use craft\helpers\StringHelper;
use craft\models\EntryType;
use craft\models\Section;
use craft\web\Controller;

class CloneController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionEntryType()
    {
        $request = Craft::$app->getRequest();

        $oldEntryId = $request->getParam('id');
        $newEntryName = $request->getParam('name');
        $newEntryHandle = StringHelper::toCamelCase($newEntryName);

        $oldEntryType = Craft::$app->getSections()->getEntryTypeById($oldEntryId);

        $entryType = Cloner::$plugin->getEntryTypes()->setupClonedEntryType($oldEntryType, $newEntryName, $newEntryHandle);

        if (!Craft::$app->getSections()->saveEntryType($entryType)) {
            Craft::$app->getSession()->setError(Craft::t('cloner', 'Couldn’t clone entry type.'));
            Cloner::error('Couldn’t clone entry type - {i}.', [ 'i' => json_encode($entryType->getErrors()) ]);

            return $this->asJson(['success' => false]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('cloner', 'Entry type cloned successfully.'));

        return $this->asJson(['success' => true]);
    }

    public function actionSection()
    {
        $request = Craft::$app->getRequest();

        $oldSectionId = $request->getParam('id');
        $newSectionName = $request->getParam('name');
        $newSectionHandle = StringHelper::toCamelCase($newSectionName);

        $oldSection = Craft::$app->getSections()->getSectionById($oldSectionId);

        $section = Cloner::$plugin->getSections()->setupClonedSection($oldSection, $newSectionName, $newSectionHandle);

        if (!Craft::$app->getSections()->saveSection($section)) {
            Craft::$app->getSession()->setError(Craft::t('cloner', 'Couldn’t clone section.'));
            Cloner::error('Couldn’t clone section - {i}.', [ 'i' => json_encode($section->getErrors()) ]);

            return $this->asJson(['success' => false]);
        }

        // Split off the default entry type
        $oldDefaultEntryType = $oldSection->getEntryTypes()[0];
        $newDefaultEntryType = $section->getEntryTypes()[0];

        // Because a new section will already have a default entry type, we want to treat that a little different
        // Instead, we just want to copy the field layout from the old section to the new one - not create a new one.
        $defaultEntryType = Cloner::$plugin->getEntryTypes()->setupDefaultEntryType($oldDefaultEntryType, $newDefaultEntryType);

        if (!Craft::$app->getSections()->saveEntryType($defaultEntryType)) {
            Craft::$app->getSession()->setError(Craft::t('cloner', 'Couldn’t clone section’s default entry type.'));
            Cloner::error('Couldn’t clone section’s default entry type - {i}.', [ 'i' => json_encode($defaultEntryType->getErrors()) ]);
        }

        foreach ($oldSection->getEntryTypes() as $key => $oldEntryType) {
            // We want to skip the default entry type - already done!
            if ($key === 0) {
                continue;
            }

            $newEntryName = $oldEntryType->name;
            $newEntryHandle = $oldEntryType->handle;

            $entryType = Cloner::$plugin->getEntryTypes()->setupClonedEntryType($oldEntryType, $newEntryName, $newEntryHandle);
            $entryType->sectionId = $section->id;

            if (!Craft::$app->getSections()->saveEntryType($entryType)) {
                Craft::$app->getSession()->setError(Craft::t('cloner', 'Couldn’t clone section’s entry type.'));
                Cloner::error('Couldn’t clone section’s entry type - {i}.', [ 'i' => json_encode($entryType->getErrors()) ]);
            }
        }

        Craft::$app->getSession()->setNotice(Craft::t('cloner', 'Section cloned successfully.'));
        
        return $this->asJson(['success' => true]);
    }

    public function actionVolume()
    {
        $request = Craft::$app->getRequest();

        $id = $request->getParam('id');
        $name = $request->getParam('name');
        $handle = StringHelper::toCamelCase($name);

        $oldVolume = Craft::$app->getVolumes()->getVolumeById($id);

        $volume = Cloner::$plugin->getVolumes()->setupClonedVolume($oldVolume, $name, $handle);

        if (!Craft::$app->getVolumes()->saveVolume($volume)) {
            Craft::$app->getSession()->setError(Craft::t('cloner', 'Couldn’t clone volume.'));
            Cloner::error('Couldn’t clone volume - {i}.', [ 'i' => json_encode($volume->getErrors()) ]);

            return $this->asJson(['success' => false]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('cloner', 'Volume cloned successfully.'));

        return $this->asJson(['success' => true]);
    }

    public function actionTransform()
    {
        $request = Craft::$app->getRequest();

        $id = $request->getParam('id');
        $name = $request->getParam('name');
        $handle = StringHelper::toCamelCase($name);

        $oldTransform = Craft::$app->getAssetTransforms()->getTransformById($id);

        $transform = Cloner::$plugin->getAssetTransforms()->setupClonedTransform($oldTransform, $name, $handle);

        if (!Craft::$app->getAssetTransforms()->saveTransform($transform)) {
            Craft::$app->getSession()->setError(Craft::t('cloner', 'Couldn’t clone transform.'));
            Cloner::error('Couldn’t clone transform - {i}.', [ 'i' => json_encode($transform->getErrors()) ]);

            return $this->asJson(['success' => false]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('cloner', 'Transform cloned successfully.'));

        return $this->asJson(['success' => true]);
    }

    public function actionCategoryGroup()
    {
        $request = Craft::$app->getRequest();

        $id = $request->getParam('id');
        $name = $request->getParam('name');
        $handle = StringHelper::toCamelCase($name);

        $oldCategoryGroup = Craft::$app->getCategories()->getGroupById($id);

        $categoryGroup = Cloner::$plugin->getCategoryGroups()->setupClonedCategoryGroup($oldCategoryGroup, $name, $handle);

        if (!Craft::$app->getCategories()->saveGroup($categoryGroup)) {
            Craft::$app->getSession()->setError(Craft::t('cloner', 'Couldn’t clone category group.'));
            Cloner::error('Couldn’t clone category group - {i}.', [ 'i' => json_encode($categoryGroup->getErrors()) ]);

            return $this->asJson(['success' => false]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('cloner', 'Category group cloned successfully.'));

        return $this->asJson(['success' => true]);
    }

    public function actionTagGroup()
    {
        $request = Craft::$app->getRequest();

        $id = $request->getParam('id');
        $name = $request->getParam('name');
        $handle = StringHelper::toCamelCase($name);

        $oldTagGroup = Craft::$app->getTags()->getTagGroupById($id);

        $tagGroup = Cloner::$plugin->getTagGroups()->setupClonedTagGroup($oldTagGroup, $name, $handle);

        if (!Craft::$app->getTags()->saveTagGroup($tagGroup)) {
            Craft::$app->getSession()->setError(Craft::t('cloner', 'Couldn’t clone tag group.'));
            Cloner::error('Couldn’t clone tag group - {i}.', [ 'i' => json_encode($tagGroup->getErrors()) ]);

            return $this->asJson(['success' => false]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('cloner', 'Tag group cloned successfully.'));

        return $this->asJson(['success' => true]);
    }

    public function actionGlobalSet()
    {
        $request = Craft::$app->getRequest();

        $id = $request->getParam('id');
        $name = $request->getParam('name');
        $handle = StringHelper::toCamelCase($name);

        $oldGlobalSet = Craft::$app->getGlobals()->getSetById($id);

        $globalSet = Cloner::$plugin->getGlobalSets()->setupClonedGlobalSet($oldGlobalSet, $name, $handle);

        if (!Craft::$app->getGlobals()->saveSet($globalSet)) {
            Craft::$app->getSession()->setError(Craft::t('cloner', 'Couldn’t clone global set.'));
            Cloner::error('Couldn’t clone global set - {i}.', [ 'i' => json_encode($globalSet->getErrors()) ]);

            return $this->asJson(['success' => false]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('cloner', 'Global set cloned successfully.'));

        return $this->asJson(['success' => true]);
    }

    public function actionUserGroup()
    {
        $request = Craft::$app->getRequest();

        $id = $request->getParam('id');
        $name = $request->getParam('name');
        $handle = StringHelper::toCamelCase($name);

        $oldUserGroup = Craft::$app->getUserGroups()->getGroupById($id);

        $userGroup = Cloner::$plugin->getUserGroups()->setupClonedUserGroup($oldUserGroup, $name, $handle);

        if (!Craft::$app->getUserGroups()->saveGroup($userGroup)) {
            Craft::$app->getSession()->setError(Craft::t('cloner', 'Couldn’t clone user group.'));
            Cloner::error('Couldn’t clone user group - {i}.', [ 'i' => json_encode($userGroup->getErrors()) ]);

            return $this->asJson(['success' => false]);
        }

        Cloner::$plugin->getUserGroups()->setupPermissions($oldUserGroup, $userGroup);

        Craft::$app->getSession()->setNotice(Craft::t('cloner', 'User group cloned successfully.'));

        return $this->asJson(['success' => true]);
    }

}