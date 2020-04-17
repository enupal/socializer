/* global Craft */

class SocializerProvider {

    constructor(settings) {
        const self = this;

        this.integrationType = typeof settings.integrationType !== 'undefined'
            ? settings.integrationType
            : '';

        // Make the sourceFormField read only
        this.disableOptions();
    }

    disableOptions() {
        const self = this;
        const integrationId = $('#integrationId').val();

        const data = {
            'integrationId': integrationId
        };

        Craft.postActionRequest('sprout-forms/integrations/get-source-form-fields', data, $.proxy(function(response, textStatus) {
            const statusSuccess = (textStatus === 'success');
            if (statusSuccess && response.success) {
                const rows = response.sourceFormFields;
                $('tbody .formField').each(function(index) {
                    const td = $(this);
                    td.empty();
                    const title = rows[index]["label"];
                    const handle = rows[index]["value"];
                    td.append('<div style="display:none;"><select readonly name="settings[' + self.integrationType + '][fieldMapping][' + index + '][sourceFormField]"><option selected value="' + handle + '">' + title + '</option></select></div><div style="padding: 7px 10px;font-size: 12px;color:#8f98a3;">' + title + ' <span class="code">(' + handle + ')</span></div>');
                });
            } else {
                Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to get the Form fields'));
            }
        }, this));

        return null;
    }
}

window.SocializerProvider = SocializerProvider;