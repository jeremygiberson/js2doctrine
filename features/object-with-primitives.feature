Feature: Support JSON Schema for an object



    Scenario: Schema defines an object with optional properties
      Given the model generator for namespace "V1"
      Given I generate models for "product.json"
      Then the class "Product" should exist
      And the "Product" attribute "id" should exist
      And the "Product" attribute "id" is of type "integer"
      And the "Product" attribute "id" is "nullable"
      And the "Product" attribute "name" should exist
      And the "Product" attribute "name" is of type "string"
      And the "Product" attribute "name" is "nullable"

    Scenario: Schema defines an object with required properties
      Given the model generator for namespace "V2"
      Given I generate models for "product-required-properties.json"
      Then the class "Product" should exist
      And the "Product" attribute "id" should exist
      And the "Product" attribute "id" is of type "integer"
      And the "Product" attribute "id" is not "nullable"
      And the "Product" attribute "name" should exist
      And the "Product" attribute "name" is of type "string"
      And the "Product" attribute "name" is not "nullable"