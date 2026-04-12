import domReady from '@roots/sage/client/dom-ready';
import { createApp } from 'vue';
import _ from "lodash";
import listings from './vue-components/listings.vue';
import vuevideo from './vue-components/video.vue';
import listingcard from './vue-components/listingcard.vue'
// import 'slick-carousel/slick/slick.min.js'; // removed - not available
import 'leaflet/dist/leaflet.js';
import leafletMarkerIcon from '@src/images/leaflet/marker-icon.png';
import leafletMarkerShadow from '@src/images/leaflet/marker-shadow.png';

/**
 * Application entrypoint
 */
domReady(async () => {
  const $slick = $('.slick:not(.slick_noinit)');
  if ($slick.length) {
    initSlick($slick, { dots: false });
  }

  function initSlick(element, params = {}) {
    let defaultParams = {
      arrows: false,
      dots: true,
      autoplay: true,
      slidesToShow: 1,
      slidesToScroll: 1,
      fade: true,
      cssEase: 'linear',
      speed: 1500,
      infinite: true,
      pauseOnHover: false,
      // slide: '.slide',// doesnt work
      touchThreshold: 20,
      rows: 0, //inline-block fix
    };
    $.each(params, function (index, value) {
      defaultParams[index] = value;
    });
    if (element.length) {
      $(element).on('init', function () {
        $(window).trigger('resize');
      });
      $(element).slick(defaultParams);
    }
  }

  function initMenu() {
    const $burgerNavigationWrapper = $('#burger-navigation-wrapper');
    const $horizontalNavigationWrapper = $('#horizontal-navigation-wrapper');
    if ($horizontalNavigationWrapper.length) {
      const $parents = $horizontalNavigationWrapper.find(
        'li.menu-item-has-children'
      );
      const $submenu = $parents.find('ul.sub-menu');
      $submenu.css('top', $submenu.closest('li').outerHeight());
      $parents.on('mouseenter', function () {
        const $li = $(this);
        let height = $li.find('> ul.sub-menu').outerHeight();
        $li.find('> ul.sub-menu > li').each((index, item) => {
          height += $(item).outerHeight();
        });
        $li.find('> a .chevron').addClass('up');
        $li.find('> ul.sub-menu').css('height', height);
      });
      $parents.on('mouseleave', function () {
        const $li = $(this);
        $li.find('> a .chevron').removeClass('up');
        $li.find('> ul.sub-menu').css('height', 0);
      });
    }
    if ($burgerNavigationWrapper.length) {
      const $trigger = $burgerNavigationWrapper.find(
        '.burger-navigation-trigger'
      );
      const $menu = $burgerNavigationWrapper.find('#navigation');
      $trigger.on('click', () => {
        $trigger.toggleClass('open');
        $menu.toggleClass('visible');
      });
      $(window).on('resize', () => {
        showOrHideBurgerMenu();
        if($trigger.hasClass('open')){
          $trigger.removeClass('open');
          $menu.removeClass('visible');
        }
      })
    }
  }

  function initAccordion() {
    const $accordions = $('.accordion');
    const $accordionsContent = $accordions.find('.accordion-content');
    const $accordionsToggle = $accordions.find('.accordion-toggle');
    $accordionsContent.hide();
    $accordions.removeClass('accordion-pre-init');
    $accordionsToggle.on('click', function () {
      const $accordion = $(this);
      const target = $accordion.data('accordion-target');
      const group = $accordion.parent('.accordion').data('accordion-group');

      if (target) {
        const $content = $accordions.find(
          '[data-accordion-anchor="' + target + '"]'
        );
        // const group = $accordion
        if ($content.length) {
          if (group) {
            closeAccordionGroup(group);
          }
          if ($content.is(':hidden')) {
            $content.slideDown(600, function () {
              //we should scroll here
            });
            $accordion.addClass('open');
            $accordion.find('.chevron').addClass('up');
          } else {
            $accordion.removeClass('open');
            $accordion.find('.chevron').removeClass('up').addClass('down');
            $content.slideUp();
          }
        }
      }
    });
    $accordions.each((index, element) => {
      //Trigger click if accordion should be expanded
      if ($(element).data('expanded') === 1) {
        $(element).find('.accordion-toggle').trigger('click');
      }
    });
  }

  function closeAccordionGroup(group, immediate = false) {
    const $accordionsgroup = $(
      '.accordion[data-accordion-group="' + group + '"]'
    );
    if(immediate) {
      $accordionsgroup.find('.accordion-content').hide();
    }else{
      $accordionsgroup.find('.accordion-content').slideUp();
    }
    $accordionsgroup.find('.accordion-toggle').removeClass('open');
    $accordionsgroup
    .find('.accordion-toggle .chevron')
    .removeClass('up')
    .addClass('down');
  }

  function initVue() {
    const config = {};
    //class for handling vue events
    window.Event = new (class {
      constructor() {
        this.vue = createApp(config);
      }

      fire(event, data = null) {
        this.vue.$emit(event, data);
      }

      listen(event, callback) {
        this.vue.$on(event, callback);
      }
    })();
    const componentElements = document.querySelectorAll('.vue-component');
    if (componentElements.length > 0) {
      componentElements.forEach((item) => {
        const app = createApp(config);
        app.component('listings', listings);
        app.component('vuevideo', vuevideo);
        app.component('listingcard', listingcard);
        app.mount(item);
      });
    }
  }

  function initShowHidden() {
    const $buttons = $('.showhidden');
    if ($buttons.length) {
      $buttons.on('click', function (event) {
        event.preventDefault();
        const container = $(this).data('container');
        if (container) {
          const $container = $(container);
          $container.find('.hidden').removeClass('hidden');
        }
        $(this).remove();
      });
    }
  }

  function initAnchorLinks() {
    const $links = $('a');
    const $accordions = $('.accordion');
    const accordiongroups = [];
      $accordions.each((index, item) => {
        const group = $(item).data('accordion-group');
        if(group && !accordiongroups.includes(group)){
          accordiongroups.push(group);
        }
    })
    $links.on('click', function (e) {
      let href = $(this).attr('href');
      if ((typeof href !== 'undefined' && href.startsWith('#')) || $(this).hasClass('anchor-link')) {
        if (!href) {
          href = $(this).find('a').attr('href');
        }
        if (href) {
          if(accordiongroups.length) {
            //close all accordion groups before scroll
            accordiongroups.forEach((group) => {
              closeAccordionGroup(group, true);
            })
          }
          let height = calculateHeaderHeight();
          let $anchor = $(href);
          if ($anchor.length) {
            if ($anchor.hasClass('accordion')) {
              const $toggle = $anchor.find('.accordion-toggle');
              if ($toggle.length && !$toggle.hasClass('open')) {
                $toggle.trigger('click');
              }
            }
            let top = $anchor.offset().top - 40;
            if (height > 0 && height < top) {
              top -= height;
            }
            $('html,body').animate({ scrollTop: top }, 1600);
            window.history.pushState(null, null, href);
            e.preventDefault();
          }
        }
      }
    });
  }

  function calculateHeaderHeight()
  {
    let height = 0;
    const $header = $('header');
    if ($header.length) {
      height += $('header').outerHeight();
      const $brand = $header.find('.brand');
      if($brand.length) {
        const $img = $brand.find('img');
        if($img.length) {
          //we have brand and image
          const brandHeight = $brand.height();
          const imgHeight = $img.height();
          if(imgHeight > brandHeight){
            //image has an offset
            height += imgHeight - brandHeight;
          }
        }
      }
    }
    return height;
  }

  function initForms() {
    //Exclusive checkboxes
    const $exclusiveCheckboxes = $('input[type="checkbox"][data-exclusive]');
    if ($exclusiveCheckboxes.length) {
      $exclusiveCheckboxes.on('click', function (e) {
        const $checkbox = $(this);
        const checkEvent = $checkbox.is(':checked');
        const $checkboxesInGroup = $(
          'input[type="checkbox"][data-exclusive="' +
            $checkbox.data('exclusive') +
            '"]'
        );
        $checkboxesInGroup.prop('checked', false); //uncheck all in group, this included
        if (checkEvent) {
          $checkbox.prop('checked', true); //check this
        }
      });
    }

    //Showingselect
    const $showingSelects = $('.showings input[name="showing"]');
    const $slotSelects = $('.showings select[name="slot"]');
    $showingSelects.on('change', function () {
      $slotSelects.prop('disabled', true);
      $showingSelects.prop('required', true);
      const isChecked = $(this).is(':checked');
      const showingId = $(this).val();
      if (isChecked) {
        $showingSelects.prop('required', false);
        if (showingId) {
          const $slotSelect = $(
            '.showings select[data-belongsto="showing-' + showingId + '"]'
          );
          if ($slotSelect.length) {
            $slotSelect.prop('disabled', false);
          }
        }
      }
    });
    if ($showingSelects.length === 1) {
      $showingSelects.trigger('click');
    }
  }

  function initOnResize() {
    $(window).on('resize', () => {
      calculateAppFooter();
    });
    $(window).trigger('resize');
  }

  function showOrHideBurgerMenu() {
    const $horizontalNavigationWrapper = $('#horizontal-navigation-wrapper');
    if ($horizontalNavigationWrapper.length) {
      const $header = $('header');
      const $banner = $header.find('.banner');
      const bannerWidth = $banner.width();
      let childrenWidth = 0;
      const $toggle = $(
        '#burger-navigation-wrapper .burger-navigation-trigger'
      );
      let height = null;
      const $links = $horizontalNavigationWrapper.find('a');
      if (!$horizontalNavigationWrapper.hasClass('!hidden')) {
        $horizontalNavigationWrapper.attr(
          'data-show-at-header-width',
          $header.width()
        );
        $banner
          .children()
          .each((index, element) => {
            childrenWidth += $(element).outerWidth();
          });
        if (childrenWidth > 0 && childrenWidth > bannerWidth) {
          //nav is too wide, hide it
          if ($toggle.hasClass('!hidden')) {
            $toggle.removeClass('!hidden');
            $horizontalNavigationWrapper.addClass('!hidden');
          }
        }
        $links.each((index, element) => {
          const currentElementHeight = $(element).height();
          if (currentElementHeight > 0) {
            if (height == null) {
              height = currentElementHeight;
            }
            if (height !== currentElementHeight) {
              //nav is too high, hide it
              if ($toggle.hasClass('!hidden')) {
                $toggle.removeClass('!hidden');
                $horizontalNavigationWrapper.addClass('!hidden');
              }
            }
          }
        });
      } else {
        if (
          $header.width() >
          $horizontalNavigationWrapper.attr('data-show-at-header-width')
        ) {
          $horizontalNavigationWrapper.removeClass('!hidden');
          $toggle.addClass('!hidden');
        }
      }
    }
  }

  function calculateAppFooter() {
    const $app = $('#app');
    const $footer = $('footer');
    if ($app.length && $footer.length) {
      const footerheight = $footer.height();
      if (footerheight) {
        $app.css('padding-bottom', footerheight);
      }
    }
  }

  function initMap() {
    const mapObjects = document.querySelectorAll('.map-object');

    if (mapObjects.length > 0) {
      const markerIcon = L.icon({
        iconUrl: leafletMarkerIcon,
        shadowUrl: leafletMarkerShadow,
        iconSize: [24, 36],
        iconAnchor: [12, 36],
      });
      mapObjects.forEach((item) => {
        let showMap = false;
        const id = item.getAttribute('id');
        if (id) {
          const dataset = item.dataset;
          if (dataset.lat && dataset.lon) {
            showMap = true;
            const zoom = dataset.zoom ?? 13;
            const map = new L.map(id, {
              zoom: zoom,
              scrollWheelZoom: 'center',
              zoomControl: false,
            }).setView([dataset.lat, dataset.lon], zoom);
            L.control
              .zoom({
                position: 'bottomleft',
              })
              .addTo(map);
            L.marker([dataset.lat, dataset.lon], {icon: markerIcon}).addTo(map);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
              attribution:
                '<a href="http://openstreetmap.org">OpenStreetMap</a>',
              maxZoom: 21,
            }).addTo(map);
          }
        }
        if (!showMap) {
          item.remove();
        }
      });
    }
  }

  function initFixedRatio() {
    const fixedRatioObjects = document.querySelectorAll('.fixed-ratio');
    fixedRatioObjects.forEach((item) => {
      const width = item.getAttribute('width');
      const height = item.getAttribute('height');
      if (width && height) {
        const ratio = Math.ceil((height / width) * 100) / 100;
        addEventListener(
          'resize',
          _.debounce(() => {
            const itemHeight = Math.round(item.offsetWidth * ratio);
            item.setAttribute('style', 'height:' + itemHeight + 'px');
          }, 100)
        );
        setTimeout(() => {
          const itemHeight = Math.round(item.offsetWidth * ratio);
          item.setAttribute('style', 'height:' + itemHeight + 'px');
        }, 400);
      }
    });
  }

  initVue();
  initMenu();
  initAccordion();
  initShowHidden();
  initAnchorLinks();
  initForms();
  initMap();
  initFixedRatio();
  initOnResize(); //should be last to trigger resize
});

/**
 * @see {@link https://webpack.js.org/api/hot-module-replacement/}
 */
import.meta.webpackHot?.accept(console.error);

// Objektsida accordion
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.accordion-trigger').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const item = this.closest('.accordion-item');
            const content = item.querySelector('.accordion-content');
            const icon = this.querySelector('.accordion-icon');
            const isOpen = item.classList.contains('open');
            item.classList.toggle('open', !isOpen);
            if (icon) icon.textContent = isOpen ? '+' : '×';
        });
    });
});
