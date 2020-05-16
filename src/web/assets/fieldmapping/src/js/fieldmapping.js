/* global Craft */

class SocializerFieldMapping {

    constructor(profileFields) {
        const self = this;
        this.profileFields = profileFields;
        // Make the sourceFormField read only
        this.disableOptions();
    }

    disableOptions() {
        const rows = this.profileFields;
        $('tbody .sourceField').each(function(index) {
            const td = $(this);
            td.empty();
            const title = rows[index]["label"];
            const handle = rows[index]["value"];
            td.append('<div style="display:none;"><select readonly name="fields[fieldMapping][' + index + '][sourceFormField]"><option selected value="' + handle + '">' + title + '</option></select></div><div style="padding: 7px 10px;font-size: 12px;color:#8f98a3;">' + title + '</div>');
        });

        return null;
    }
}

window.SocializerFieldMapping = SocializerFieldMapping;