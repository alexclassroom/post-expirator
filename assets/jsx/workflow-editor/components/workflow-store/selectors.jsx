// import { createRegistrySelector } from '@wordpress/data';
import { toPng } from 'html-to-image';

export const getPostType = (state) => {
    return state.postType;
};

export const getNodes = (state) => {
    return state.nodes;
};

export const getEdges = (state) => {
    return state.edges;
};

export const getNodeById = (state, id) => {
    return state.nodes.find(node => node.id === id);
};

export const getEdgeById = (state, id) => {
    return state.edges.find(edge => edge.id === id);
};

export const getSelectedNodes = (state) => {
    return state.selectedNodes;
};

export const getSelectedEdges = (state) => {
    return state.selectedEdges;
};

export const hasSelectedNodes = (state) => {
    return state.selectedNodes.length > 0;
};

export const hasSelectedEdges = (state) => {
    return state.selectedEdges.length > 0;
};

export const getWorkflowStatus = (state) => {
    return state.workflow.status;
}

export const getWorkflow = (state) => {
    return state.workflow;
};

export const getEditedWorkflowAttributes = (state) => {
    return state.editedWorkflowAttributes;
};

export const getEditedWorkflowAttribute = (state, key) => {
    const attributes = state.editedWorkflowAttributes;

    if (!attributes.hasOwnProperty(key)) {
        return state.workflow[key];
    }

    return state.editedWorkflowAttributes[key];
};

export const isLoadingWorkflow = (state) => {
    return !! state.isLoadingWorkflow;
}

export const isCreatingWorkflow = (state) => {
    return !! state.isCreatingWorkflow;
}

export const getEditedWorkflow = (state) => {
    return {
        ...state.workflow,
        ...state.editedWorkflowAttributes,
    };
}

export const isEditedWorkflowDirty = (state) => {
    return Object.keys(state.editedWorkflowAttributes).length > 0;
}

export const isWorkflowFlowEmpty = (state) => {
    return state.workflow.flow.trim() === '';
}

export const isNewWorkflow = (state) => {
    return !! state.isNewWorkflow;
}

export const getInitialViewport = (state) => {
    return state.initialViewport;
}

export const isSavingWorkflow = (state) => {
    return !! state.isSavingWorkflow;
}

export const isPublishedWorkflow = (state) => {
    return state.workflow.status === 'publish';
}

export const isDeletingWorkflow = (state) => {
    return !! state.isDeletingWorkflow;
}

export const isAutosavingWorkflow = (state) => {
    return !! state.isAutosavingWorkflow;
}

export const isEditedWorkflowSaveable = (state) => {
    let title;

    if (state.editedWorkflowAttributes.hasOwnProperty('title')) {
        title = state.editedWorkflowAttributes.title;
    } else {
        title = state.workflow.title;
    }

    return Object.keys(state.editedWorkflowAttributes).length > 0
        && !state.isSavingWorkflow
        && !state.isAutosavingWorkflow
        && String(title).trim() !== '';
}

export const isCurrentWorkflowPublished = (state) => {
    return state.isCurrentWorkflowPublished;
}

export const getSelectedElementsCount = (state) => {
    return state.selectedNodes.length + state.selectedEdges.length;
}

export const getSelectedNodesCount = (state) => {
    return state.selectedNodes.length;
}

export const getSelectedEdgesCount = (state) => {
    return state.selectedEdges.length;
}

export const getDataTypes = (state) => {
    return state.dataTypes;
}

export const getDataTypeByName = (state, name) => {
    return state.dataTypes.find(dataType => dataType.name === name);
}

export function takeScreenshot() {
    return new Promise((resolve, reject) => {
        try {
            toPng(document.querySelector('.react-flow'), {
                filter: (node) => {
                    // We don't want to add the minimap and the controls to the image
                    if (
                        node?.classList?.contains('react-flow__minimap') ||
                        node?.classList?.contains('react-flow__controls') ||
                        node?.classList?.contains('pwe-node-edit-button')
                    ) {
                        return false;
                    }

                    return true;
                },
            })
                .then((dataUrl) => {
                    resolve(dataUrl); // Resolve with the image dataUrl
                })
                .catch((error) => {
                    reject(error); // Reject with the error
                });
        } catch (error) {
            reject(error); // Reject with the error
        }
    });
}

export function getGlobalVariables(state) {
    return state.globalVariables;
}

export function getGlobalVariable(state, name) {
    return state.globalVariables.find(variable => variable.name === name);
}

export function getTaxonomyTerms(state, taxnomy) {
    return state.taxonomyTerms[taxnomy] || [];
}

export function getBaseSlugCounts(state) {
    return state.baseSlugCounts;
}

export function getNodeErrors(state, nodeId) {
    return state.nodeErrors[nodeId] || [];
}

export function getDraggingFromHandle(state) {
    return state.draggingFromHandle;
}

export function isConnectingNodes(state) {
    return !!state.isConnectingNodes;
}

export function getRayDebugShowQueries(state) {
    return state.rayDebug.showQueries;
}

export function getRayDebugShowEmails(state) {
    return state.rayDebug.showEmails;
}

export function getRayDebugShowWordPressErrors(state) {
    return state.rayDebug.showWordPressErrors;
}