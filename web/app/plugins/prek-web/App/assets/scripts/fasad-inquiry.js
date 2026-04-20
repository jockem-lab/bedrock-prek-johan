if (typeof Prek === 'undefined' || Prek === null) {
  var Prek = {};
}
if (typeof Prek.FasadInquiry === 'undefined' || Prek.FasadInquiry === null) {
  Prek.FasadInquiry = {};
}

(function($) {
  'use strict';
  const fasadInterestUrl = 'https://crm.fasad.eu/api/customerqueue/addinterestcustomertoqueue?';
  const fasadShowingUrl = 'https://crm.fasad.eu/api/customerqueue/addshowingcustomertoqueue?';
  let boundInquiryEvent = false;

  Prek.FasadInquiry.init = function(inquiryformholder, callback, inquiryformholderString) {
    const self = this;
    $(document).ready(function() {
      // We can either pass a selector or a jQuery object
      if (typeof inquiryformholder === 'string') {
        self.inquiryformholder = inquiryformholder;
        self.$inquiryformHolder = $(inquiryformholder);
      } else if (typeof inquiryformholder === 'object') {
        self.inquiryformholder = inquiryformholderString;
        self.$inquiryformHolder = inquiryformholder;
      }

      // There are several holders, set up form for each individually
      if (self.$inquiryformHolder.length > 1) {
        self.$inquiryformHolder.each(function(index, element){
          Prek.FasadInquiry.init($(element), callback, inquiryformholder);
        });
        return;
      }
      self.$inquiryform = self.getForm(self.$inquiryformHolder);
      if (!self.$inquiryform) {
        // No forms on this page
        return;
      }

      self.$inquiryMessageHolder = self.getMessageHolder(self.$inquiryform);
      self.fasadFormData = fasadFormData; // From \PrekWeb\Includes\Fasad::inquiryScripts()
      self.callback = callback;

      let instance = $.extend(true, {}, self);
      if (!instance.boundInquiryEvent) {
        instance.bindInquirySubmit(instance);
        instance.boundInquiryEvent = true;
      }
    });
  };

  Prek.FasadInquiry.bindInquirySubmit = function(instance) {
    const self = instance;
    if (self.$inquiryform !== null) {
      if (self.$inquiryform.hasClass("wpcf7-form")) {

        //Disable submit button while submit to prevent double submissions of form.
        $("form.wpcf7-form").on("submit", function() {
          const $form = $(this);
          if (self.isActiveForm($form)) {
            self.disableSubmit($form);
          }
        });

        document.addEventListener("wpcf7submit", function(event) {
          const $form = $(event.target);
          if (self.isActiveForm($form)) {
            self.enableSubmit($form);
          }
        }, false);

        $(document).on("wpcf7mailsent", function(event) {
          // Form submitted successfully
          const $form = $(event.target);
          if (self.isActiveForm($form)) {
            self.addToFasadQueue(event.target, self);
          }
        });
      } else if (self.$inquiryform.hasClass("hf-form")) {

        //Disable submit button while submit to prevent double submissions of form.
        html_forms.on("submit", function(formElement) {
          const $form = $(formElement);
          if (self.isActiveForm($form)) {
            self.disableSubmit($form);
          }
        });

        html_forms.on("submitted", function(formElement) {
          const $form = $(formElement);
          if (self.isActiveForm($form)) {
            self.enableSubmit($form);
          }
        }, false);

        html_forms.on("message", function(formElement) {
          const $form = $(formElement);
          if (self.isActiveForm($form)) {
            // Remove original message holder (we add our own)
            const $originalMessageHolder = $(formElement).find(".hf-message");
            if ($originalMessageHolder.length) {
              if (!$originalMessageHolder.hasClass("hf-message-success")) {
                // Put error message into our own message holder
                const $inquiryMessageHolder = self.getMessageHolder($form);
                $inquiryMessageHolder.html($originalMessageHolder.text());
              }
              $originalMessageHolder.remove();
            }
          }
        });
        html_forms.on("success", function(formElement) {
          const $form = $(formElement);
          if (self.isActiveForm($form)) {
            // Form submitted succesfully
            self.addToFasadQueue(formElement, self);
          }
        });
      } else {
        self.$inquiryform.on("submit", function(e) {
          //TODO: needs to be thoroughly tested
          e.preventDefault();
          self.addToFasadQueue(e.target, self);
        });
      }
    }
  };

  Prek.FasadInquiry.isActiveForm = function($target) {
    const self = this;
    if ($target.length && self.$inquiryform.length) {
      return $target[0] === self.$inquiryform[0];
    }
    return false;
  };

  Prek.FasadInquiry.disableSubmit = function($form) {
    const button = $form.find('input[type=submit]');
    const current_val = button.val();

    // store the current value so we can reset it later
    button.attr('data-value', current_val);

    // disable the button
    button.prop('disabled', true);

    // tell the user what's happening
    button.val('Skickas');
  };

  Prek.FasadInquiry.enableSubmit = function($form) {
    // find only disabled submit buttons
    const button = $form.find('input[type=submit]:disabled');
    // grab the old value
    const old_value = button.attr('data-value');

    // enable the button
    button.prop('disabled', false);

    // put the old value back in
    button.val(old_value);
  };

  Prek.FasadInquiry.getForm = function($target) {
    let $form = $target;
    if (!$form.is('form')) {
      $form = $form.find('form');
    }
    if ($form.parents(self.inquiryformholder).length === 0) {
      return false;
    }
    return $form;
  };

  Prek.FasadInquiry.getMessageHolder = function($form) {
    let $messageHolder = '';
    if ($form.hasClass('wpcf7-form')) {
      $messageHolder = $form.find('.wpcf7-response-output');
    } else if ($form.hasClass('hf-form')) {
      $messageHolder = $form.find(".fasad-response");
      if (!$messageHolder.length) {
        $messageHolder = $("<div></div>").addClass("fasad-response");
        $form.append($messageHolder);
      }
    } else {
      $messageHolder = $form.find('div.popup-message');
    }
    return $messageHolder;
  };

  Prek.FasadInquiry.getInquiryType = function($form) {
    const formValues = $form.serializeArray();
    let type = '';
    let showing = '';
    let fkobject = '';
    let fkcorporation = '';
    $.each(formValues, function(index, formValue) {
      if (formValue.name === 'fkobject' && formValue.value > 0) {
        fkobject = formValue.value;
      }
      if (formValue.name === 'fkcorporation' && formValue.value > 0) {
        fkcorporation = formValue.value;
      }
      if (formValue.name === 'showing' && formValue.value > 0) {
        showing = formValue.value;
      }
    });
    if (showing && fkobject) {
      type = 'showing';
    } else if (fkobject) {
      type = 'interest';
    } else if (fkcorporation) {
      type = 'speculator';
    }
    return type;
  }

  Prek.FasadInquiry.honeypotSubmitted = function($form, self) {
    let honeypotSubmitted = false;
    if (self.fasadFormData.hasOwnProperty('honeypotField') && self.fasadFormData.honeypotField !== '') {
      const $honeypotField = $form.find('[name="' + self.fasadFormData.honeypotField + '"]');
      if ($honeypotField.length) {
        if ($honeypotField.val() !== '') {
          honeypotSubmitted = true;
        }
      }
    }
    return honeypotSubmitted;
  };

  Prek.FasadInquiry.getFormValuesWithMessage = function($form) {
    let message = '';
    const $textareaMessage = $form.find('[name="message"]');
    //get message from textarea and disable to remove from serialize (we do it ourself)
    if ($textareaMessage.length) {
      message = $textareaMessage.val() + '%0D%0A';
      $textareaMessage.attr('disabled', true);
    }
    const $fields = $form.find('.message_append');
    if ($fields.length) {
      $.each($fields, function() {
        const value = [];
        let label = '';
        let $inputs = [];
        if ($(this).is('input')) {
          $inputs.push($(this));
        } else {
          $inputs = $(this).find('input');
        }
        label = $(this).data('name');
        if (!label) {
          label = $(this).prop('placeholder');
        }
        if ($inputs.length) {
          $.each($inputs, function() {
            if ($(this).attr('type') == 'checkbox') {
              if ($(this).is(':checked')) {
                value.push($(this).val());
              }
            } else {
              if ($(this).val() !== '') {
                value.push($(this).val());
              }
            }
          });
          if (value.length) {
            message += label + ': ' + value.join() + '%0D%0A';
          }
        }
      });
    }
    if (message !== '') {
      message = message.replace(' ', '+');
    }
    let serializedValues = $form.serialize();

    //reset disabled field
    if ($textareaMessage.length) {
      $textareaMessage.attr('disabled', false);
    }
    if (message !== '') {
      serializedValues += '&message=' + message.replace(' ', '+');
    }
    return serializedValues;
  };

  Prek.FasadInquiry.addToFasadQueue = function(target, instance) {
    const self = instance;
    const $form = self.getForm($(target));
    const $inquiryMessageHolder = self.getMessageHolder($form);
    self.resetMessage($form, $inquiryMessageHolder);

    if ($form) {
      const inquiryType = self.getInquiryType($form);
      if (inquiryType) {
        let sendToFasad = !self.honeypotSubmitted($form, self); //If honeypot field exists and has value, do not send
        self.handleSlots($form, inquiryType);
        const serializedValues = self.getFormValuesWithMessage($form);
        if (sendToFasad) {
          if (inquiryType === 'showing') {
            self.addToFasadShowingQueue(serializedValues, self, $form, $inquiryMessageHolder);
          } else {
            self.addToFasadStakeholderQueue(serializedValues, self, $form, inquiryType, $inquiryMessageHolder);
          }
        } else {
          self.resetForm($form); //Reset form even if the form isn't submitted to fasad (act normal)
        }
      }
    }
  };

  Prek.FasadInquiry.addToFasadStakeholderQueue = function(values, instance, $form, inquiryType) {
    const self = instance;
    values += '&policytypeid=3';
    const sendToFasadStakeholderQueue = $.ajax({
      url: fasadInterestUrl,
      data: values,
      dataType: 'jsonp',
    });

    const $inquiryMessageHolder = self.getMessageHolder($form);
    sendToFasadStakeholderQueue.done(function(response) {
      let msg = '';
      if (!response.hasOwnProperty('success')) {
        response.success = 1;
      }
      if (response.success) {
        msg = inquiryType === "interest" ? self.fasadFormData.interestSuccessMessage : self.fasadFormData.speculatorSuccessMessage;
        self.resetForm($form);
      } else {
        self.rePopulateForm($form, values);
        msg = response.message;
      }
      if ($inquiryMessageHolder.length) {
        self.resetMessage($form, $inquiryMessageHolder);
        $inquiryMessageHolder.html(msg);
      }
      if (self.callback) {
        self.callback(self, $form, response, msg);
      }
    });
  };

  Prek.FasadInquiry.addToFasadShowingQueue = function(values, instance, $form, $inquiryMessageHolder) {
    const self = instance;
    values += '&policytypeid=4';

    const sendToFasadShowingQueue = $.ajax({
      url: fasadShowingUrl,
      data: values,
      dataType: 'jsonp',
    });

    sendToFasadShowingQueue.done(function(response) {
      const params = new URLSearchParams(values);
      const showing = params.get("showing");
      const slot = params.get("slot");

      let msg = '';
      let responseClass = '';
      if (response.success) {
        msg = self.fasadFormData.showingSuccessMessage;
        self.resetForm($form);
        responseClass = 'sent';
      } else {
        msg = (response.message.trim() === 'Visningen är fullbokad!') ?
          self.fasadFormData.showingFullMessage :
          response.message;
        self.rePopulateForm($form, values);
        responseClass = 'failed';
      }
      if ($inquiryMessageHolder.length) {
        self.resetMessage($form, $inquiryMessageHolder);
        $form.addClass(responseClass);
        $inquiryMessageHolder.html(msg);
      }
      if (self.callback) {
        self.callback(self, $form, response, msg, showing, slot);
      }
    });
  };

  Prek.FasadInquiry.handleSlots = function($form, inquiryType) {
    if (inquiryType === 'showing') {
      const $showingInputs = $form.find('.showings [name="showing"]');
      const $slotSelects = $form.find('.showings [name="slot"]');
      const showingId = $showingInputs.filter(':checked').val();

      if ($slotSelects.length > 1) {
        $slotSelects.attr('disabled', 'disabled').addClass('slot-disabled');
        $form.find('[data-belongsto="showing-' + showingId + '"]').prop('disabled', false).removeClass('slot-disabled');
      }
    }
  };

  Prek.FasadInquiry.rePopulateForm = function($form, values) {
    // CF7 resets the form, so repopulate if error
    $.each(values.split('&'), function (index, elem) {
      let vals = elem.split('=');
      vals[1] = decodeURIComponent(vals[1]);
      let $element = $form.find("[name='" + vals[0] + "']");
      if ($element.is('select')) {
        $element.find('option[value="' + vals[1] + '"]').attr('selected', 'selected').trigger('change');
      } else if ($element.is(':radio')) {
        if (vals[0] === 'showing') {
          $element.filter('[value="' + vals[1] + '"]').prop('checked', true).trigger('change');
        } else {
          $element.prop('checked', true).trigger('change');
        }
      } else {
        $element.val(vals[1]);
      }
    });
    $form.find('.slot-disabled').prop('disabled', false).removeClass('slot-disabled');
  };

  Prek.FasadInquiry.resetForm = function($form) {
    $form.find('input[type="text"]').val('');
    $form.find('input[type="email"]').val('');
    const $firstShowingInput = $form.find('input[name="showing"]').first();
    if ($firstShowingInput.length) {
      $firstShowingInput.trigger('change');
    }
    $form.find('textarea').val('');
    $form.find('.slot-disabled').prop('disabled', false).removeClass('slot-disabled');
  };

  Prek.FasadInquiry.resetMessage = function($form, $inquiryMessageHolder) {
    if ($form.length) {
      // Remove CF7 classes
      $form.removeClass('sent failed aborted spam invalid unaccepted');
    }
    if ($inquiryMessageHolder.length) {
      $inquiryMessageHolder.html('');
    }
  };

})(jQuery);
