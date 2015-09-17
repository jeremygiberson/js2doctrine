Feature: Support JSON Schema for an object



    Scenario: Schema defines an object with optional properties
      Given the model generator for namespace "V1"
      Given I generate models for "product.json"
      Then the class "Product" should exist
      And the "Product" attribute "id" should exist
      And the "Product" attribute "id" annotations should contain '@Column(type="integer")'
      And the "Product" attribute "name" should exist
      And the "Product" attribute "name" annotations should contain '@Column(type="string")'

    Scenario: Schema defines an object with required properties