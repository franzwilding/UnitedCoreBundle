# UnitedCoreBundle

![Codeship Status](https://codeship.com/projects/4669f350-b065-0132-0073-3a7a9fb44a4e/status?branch=master)

Symfony2 Content Management System toolset for developers, not site builders. United will help you to rapidly build a custom CMS for your next project but will not blow up your code. 

## Status
**United is currently under heavy development. A first pre alpha version will be ready in April 2015** 

## Features
- **No base system** - you create a custom CMS for each project by using United's components
- **No hacking** - Extend United for your project instead of hacking it
- **Modular** - install other bundles to add functionality like media management 
- **Separated theme** - You don't need to use the default layout you can easily create your own
- **Built for developers** - Implement your CMS like any other symfony2 application - but with a powerful toolset of base controllers, services and ready-to-use components

# Installation

Install symfony
    
    composer create-project symfony/framework-standard-edition
        
    
Install UnitedCore and a theme (UnitedOne, the default theme, requires UnitedCore so we don't need to)
    
    composer require franzwilding/united-one-bundle 

Register United bundles 

    new United\CoreBundle\UnitedCoreBundle(),
    new United\OneBundle\UnitedOneBundle(), 

**Check out the Getting started tutorial (Comming soon)**