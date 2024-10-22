export function SiteData() {
    return {
        name: "site",
        label: "Site",
        type: "object",
        objectType: "site",
        propertiesSchema: [
            {
                name: "name",
                type: "string",
                label: "Name",
            },
            {
                name: "description",
                type: "string",
                label: "Description",
            },
            {
                name: "url",
                type: "string",
                label: "Site URL",
            },
            {
                name: "home_url",
                type: "string",
                label: "Home URL",
            },
            {
                name: "admin_email",
                type: "email",
                label: "Admin Email",
            },
        ],
    };
}

export default SiteData;