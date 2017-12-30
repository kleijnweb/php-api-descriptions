#### Hydration

Different processors handle NULL and undefined in hydration input slightly different:

 - `StrictSimpleObjectProcessor` (`additionalProperties: false`)
 
    - If property not in schema: omit.
    - Else: delegate value determination to property processor (even if NULL)

 - `LooseSimpleObjectProcessor` (`additionalProperties: true|undefined`)

    - If property not in schema:
      - If property value exists: delegate value determination to value processor (`AnyProcessor`)
      - Else: omit
    - Else if NULL and property default exists: set default
    - Else: delegate value determination to property processor

 - `ComplexTypeProcessor` (Has a `ComplexType`)

    - If property not in schema: omit
    - If property not class: omit
    - Else: delegate value determination to property processor

 - `ArrayProcessor` (`ArraySchema`)

    - If value NULL:
       - If property default exists: set default
       - Else: return NULL
    - Else: delegate value determination to items processor

 - `ScalarProcessor` (Primitives, including strings to be interpreted as `DateTime`)

    - If value NULL:
       - If property default exists: set default
       - Else: return NULL

#### Dehydration

When dehydrating, NULL values are handled by before mentioned processors as follows:

 - `StrictSimpleObjectProcessor`
 
    - If property not in schema: fail (`OutOfBoundsException`)
    - Else: delegate value determination to property processor

 - `LooseSimpleObjectProcessor`

    - If property not in schema:
      - If property value exists: delegate to value processor (`AnyProcessor`)
    - Else: delegate value determination to property processor

 - `ComplexTypeProcessor`

    - If property in schema not in class:
      - If default exists: set default
      - Else: omit
    - Else if property value NULL:
       - If property *type* NULL: set NULL
       - Else: omit 
    - Else: delegate value determination to property processor

 - `ArrayProcessor` (`ArraySchema`)

    - Pass through, delegate value determination to items processor

 - `ScalarProcessor`

    - Pass through
