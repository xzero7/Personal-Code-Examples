import PropTypes from 'prop-types'
import styled, { css } from 'styled-components'

import Icon from 'components/Icon'
import { breakpoint } from 'styles/functions'
import variables from 'styles/variables'

const {
  breakLarge,
  colorGrayLightest,
  borderRadius
} = variables

export const CssStarRating = styled.div`
  padding: 0.5rem 0.75rem;
  background: ${colorGrayLightest};
  border-radius: ${borderRadius};
  cursor: default;

  ${props => /plain/.test(props.modifier) && css`
    padding: 0;
    background: none;
  `}
`

const CssStarContainer = styled.div`
  position: relative;
  display: inline-block;
`

const CssStarMask = styled.span`
  display: block;
  position: absolute;
  width: 1.3rem;
  height: 1.3rem;
  overflow: hidden;

  ${props => /large/.test(props.modifier) && css`
    ${breakpoint(breakLarge)} {
      width: 1.8rem;
      height: 1.8rem;
    }
  `}
`
export const CssStar = styled.span`
  display: inline-block;
  margin-right: -1px;
`

export const CssIcon = styled(Icon)`
  ${CssStar} & {
    width: 1.3rem;
    height: 1.3rem;
  }

  ${props => props.color && css`
  fill: ${props.color};
`}
`

/**
 *  A component to be used to add a 5 Star rating on a Page.
 */
const StarRating = ({ className, starRating, color }) => {
  if (starRating) {
    const starIcons = []
    for (var i = 0; i < 5; i++) {
      if (i < starRating) {
        starIcons.push(<CssStarContainer key={i.toString()}><CssStarMask /><CssStar><CssIcon modifier='star' color={color} /></CssStar></CssStarContainer>)
      } else {
        starIcons.push(<CssStarContainer key={i.toString()}><CssStar><CssIcon modifier='star' color='gray' /></CssStar></CssStarContainer>)
      }
    }
    return (
      <CssStarRating className={className}>
        <meta property='reviewRating' content={starRating} />
        {starIcons}
        <span id='show_rating' className='visually-hidden'>Rating: {starRating} out of 5 stars</span>
      </CssStarRating>
    )
  } else {
    return null
  }
}

export const modifiers = ['plain', 'large']
StarRating.propTypes = {
  starRating: PropTypes.number,
  modifier: PropTypes.string
}

export default StarRating
