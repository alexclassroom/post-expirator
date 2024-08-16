/*
 * Copyright (c) 2023. PublishPress, All rights reserved.
 */

import {
    SettingsForm,
    SettingsSection,
    PostTypesSettingsPanels,
    SubmitButton,
    ButtonsPanel,
    NonceControl
} from "./components";

import { StrictMode, useState, useEffect } from "@wordpress/element";

import { createRoot } from 'react-dom/client';

import {
    nonce,
    referrer,
    settings,
    expireTypeList,
    taxonomiesList,
    text,
    statusesList
} from "&config.settings-post-types";
import { render } from "react-dom";

const SettingsFormPanel = (props) => {
    const [formValidationStatusPerPostType, setFormValidationStatusPerPostType] = useState({});
    const [pendingValidationPerPostType, setPendingValidationPerPostType] = useState({});
    const [allValid, setAllValid] = useState(true);
    const [hasNoPendingValidation, setHasNoPendingValidation] = useState(true);

    useEffect(() => {
        let allFormsAreValid = true;

        for (const [postType, isValidForPostType] of Object.entries(formValidationStatusPerPostType)) {
            if (!isValidForPostType) {
                allFormsAreValid = false;
                break;
            }
        }

        setAllValid(allFormsAreValid);
    }, [formValidationStatusPerPostType]);

    useEffect(() => {
        let hasNoPendingValidation = true;

        for (const [postType, hasPending] of Object.entries(pendingValidationPerPostType)) {
            if (hasPending) {
                hasNoPendingValidation = false;
                break;
            }
        }

        setHasNoPendingValidation(hasNoPendingValidation);
    }, [pendingValidationPerPostType]);

    const onDataIsValid = (postType) => {
        formValidationStatusPerPostType[postType] = true;
        setFormValidationStatusPerPostType({...formValidationStatusPerPostType});
    }

    const onDataIsInvalid = (postType) => {
        formValidationStatusPerPostType[postType] = false;
        setFormValidationStatusPerPostType({...formValidationStatusPerPostType});
    }

    const onValidationStarted = (postType) => {
        pendingValidationPerPostType[postType] = true;
        setPendingValidationPerPostType({...pendingValidationPerPostType});
    }

    const onValidationFinished = (postType) => {
        pendingValidationPerPostType[postType] = false;
        setPendingValidationPerPostType({...pendingValidationPerPostType});
    }

    const saveButtonText = hasNoPendingValidation ? text.saveChanges : text.saveChangesPendingValidation;

    return (
        <StrictMode>
            <SettingsForm>
                <NonceControl
                    name="_postExpiratorMenuDefaults_nonce"
                    nonce={nonce}
                    referrer={referrer}
                />
                <SettingsSection
                    title={text.settingsSectionTitle}
                    description={text.settingsSectionDescription}>
                    <PostTypesSettingsPanels
                        settings={settings}
                        text={text}
                        expireTypeList={expireTypeList}
                        taxonomiesList={taxonomiesList}
                        statusesList={statusesList}
                        onDataIsValid={onDataIsValid}
                        onDataIsInvalid={onDataIsInvalid}
                        onValidationStarted={onValidationStarted}
                        onValidationFinished={onValidationFinished}
                    />
                </SettingsSection>

                <ButtonsPanel>
                    <SubmitButton
                        id="expirationdateSaveDefaults"
                        name="expirationdateSaveDefaults"
                        disabled={!allValid || !hasNoPendingValidation}
                        text={saveButtonText}
                    />
                </ButtonsPanel>
            </SettingsForm>
        </StrictMode>
    )
};

const container = document.getElementById("publishpress-future-settings-post-types");
const component = (<SettingsFormPanel />);

createRoot(container).render(component);
